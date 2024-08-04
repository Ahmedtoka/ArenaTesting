<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Order;
use App\Models\CashRegisterTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class CashRegisterController extends Controller
{      

    public function __construct() {
        $this->middleware('auth');
    }
    /**
     * Returns number of opened Cash Registers for the
     * current logged in user
     *
     * @return int
     */
    public function countOpenedRegister()
    {
        $user_id = auth()->user()->id;
        $count =  CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->count();
        return $count;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     return view('cash_register.index');
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        if ($this->countOpenedRegister() != 0) {
            return redirect(route('sale.pos'));
        }
        //Check if there is a open register, if yes then redirect to POS screen.
        
        return view('orders.open-register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $request->amount;
            }
            
            $user_id = auth()->user()->id;
            $warehouse_id = auth()->user()->warehouse_id;

            $register = CashRegister::create([
                        'warehouse_id' => $warehouse_id,
                        'user_id' => $user_id,
                        'status' => 'open',
                        'created_at' => Carbon::now()->format('Y-m-d H:i:00')
                    ]);
            
            $trans = $register->cash_register_transactions()->create([
                            'amount' => $initial_amount,
                            'pay_method' => 'Cash',
                            'type' => 'credit',
                            'transaction_type' => 'initial'
                        ]);
        } catch (\Exception $e) {
            dd($e);
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }

        return redirect(route('sale.pos'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     $register_details =  $this->getRegisterDetails($id);
    //     $user_id = $register_details->user_id;
    //     $open_time = $register_details['open_time'];
    //     $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
    //     $details = $this->getRegisterTransactionDetails($user_id, $open_time, $close_time);

    //     $payment_types = $this->payment_types();

    //     return view('cash_register.register_details')
    //                 ->with(compact('register_details', 'details', 'payment_types', 'close_time'));
    // }

    /**
     * Get the transaction details for a particular register
     *
     * @param $user_id int
     * @param $open_time datetime
     * @param $close_time datetime
     *
     * @return array
     */
    public function getRegisterTransactionDetails($user_id, $open_time, $close_time)
    {

        $transaction_details = Order::where('location_id', $user_id)
                ->whereBetween('created_at', [$open_time, $close_time])
                ->select(
                    DB::raw('SUM(total_tax) as total_tax'),
                    DB::raw('SUM(total_discounts) as total_discount'),
                    DB::raw('SUM(total_price) as total_sales'),
                    DB::raw('COUNT(id) as total_invoices')
                )
                ->first();

        return ['transaction_details' => $transaction_details];

    }

    /**
     * Retrieves details of given rigister id else currently opened register
     *
     * @param $register_id default null
     *
     * @return object
     */
    public function getRegisterTransactions($register_id = null)
    {
        $query = CashRegister::join(
            'cash_register_transactions as ct',
            'ct.cash_register_id',
            '=',
            'cash_registers.id'
        )
        ->join(
            'users as u',
            'u.id',
            '=',
            'cash_registers.user_id'
        )


        ->leftJoin(
            'warehouses as bl',
            'bl.id',
            '=',
            'cash_registers.warehouse_id'
        );
        if (empty($register_id)) {
            $user_id = auth()->user()->id;
            $query->where('user_id', $user_id)
                ->where('cash_registers.status', 'open');
        } else {
            $query->where('cash_registers.id', $register_id);
        }
        

        $register_details = $query->select(
            'cash_registers.created_at as open_time',
            'cash_registers.created_at as numeberof',
            'cash_registers.closed_at as closed_at',
            'cash_registers.user_id',
            'cash_registers.closing_note',
            'cash_registers.warehouse_id',
            'u.email',
            'bl.name as warehouse_name',
            DB::raw("SUM(IF(ct.transaction_type='initial', amount, 0)) as open_amount"),
            DB::raw("(IF(ct.transaction_type='initial', sale_id, 1)) as New_sale"),
            DB::raw("SUM(IF(ct.transaction_type='sell', amount, 0)) as total_pure_sale"),
            DB::raw("SUM(IF(ct.transaction_type='sell', amount, IF(ct.transaction_type='refund', -1 * amount, 0))) as total_sale"),
            DB::raw("SUM(IF(pay_method='Cash', IF(ct.transaction_type='sell', amount, 0), 0)) as total_cash"),
            DB::raw("SUM(IF(pay_method='Premium', IF(ct.transaction_type='sell', amount, 0), 0)) as total_cheque"),
            DB::raw("SUM(IF(pay_method='Credit Card', IF(ct.transaction_type='sell', amount, 0), 0)) as total_card"),
            DB::raw("SUM(IF(pay_method='Deposit', IF(ct.transaction_type='sell', amount, 0), 0)) as total_bank_transfer"),
            DB::raw("SUM(IF(ct.transaction_type='refund', amount, 0)) as total_refund"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Cash', amount, 0), 0)) as total_cash_refund"),
            DB::raw("SUM(IF(ct.transaction_type='online_refund', IF(pay_method='Cash', amount, 0), 0)) as total_online_refund"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Premium', amount, 0), 0)) as total_cheque_refund"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Credit Card', amount, 0), 0)) as total_card_refund"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Credit Card', amount, 0), 0)) as total_card_refund"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Deposit', amount, 0), 0)) as total_bank_transfer_refund"),
            DB::raw("SUM(IF(ct.transaction_type='expense', IF(pay_method='Cash', amount, 0), 0)) as total_expense"),
            DB::raw("SUM(IF(pay_method='Premium', 1, 0)) as total_cheques"),
            DB::raw("SUM(IF(pay_method='Credit Card', 1, 0)) as total_card_slips"),
            DB::raw("SUM(IF(pay_method='Cash', 1, 0)) as total_Cash_only"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Cash', 1, 0), 0)) as total_cash_refund_only"),
            DB::raw("SUM(IF(ct.transaction_type='online_refund', IF(pay_method='Cash', 1, 0), 0)) as total_online_refund_only"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Premium', 1, 0), 0)) as total_cheque_refund_only"),
            DB::raw("SUM(IF(ct.transaction_type='refund', IF(pay_method='Credit Card', 1, 0), 0)) as total_card_refunds_only")
        )->groupBy(
        'cash_registers.created_at',
        'ct.transaction_type',
        'ct.sale_id',
        'cash_registers.closed_at',
        'cash_registers.user_id',
        'cash_registers.closing_note',
        'cash_registers.warehouse_id',
        'u.email',
        'bl.name',
        'open_time'
        )
        ->orderBy('total_sale','DESC')->first();

        return $register_details;
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails()
    {
        $register_details =  $this->getRegisterTransactions();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = Carbon::now()->toDateTimeString();

        $payment_types = $this->payment_types($register_details->location_id);
        
        return view('cash_register.register_details')
                ->with(compact('register_details', 'details', 'payment_types', 'close_time'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister(CashRegister $id)
    {
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')->first();
        $register_details = $this->getRegisterTransactions();
        
        $open_time = $register_details['open_time'];
        $close_time = Carbon::now()->toDateTimeString();
        

        $details = $this->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        
        return view('orders.close-register')
            ->with(compact('register_details', 'details', 'register'));

    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request, CashRegister $cashRegister)
    {
        try {
            $register_details =  $this->getRegisterTransactions();
            $close_status_amount = $register_details->total_cash + $register_details->open_amount - $register_details->total_cash_refund -$register_details->total_online_refund - $register_details->total_expense - $request->register_close_amount;

            if(($register_details->open_amount + $register_details->total_cash - $register_details->total_cash_refund - $register_details->total_online_refund - $register_details->total_expense) < $request->register_close_amount) {
                $close_status = 'positive';
            }elseif(($register_details->open_amount + $register_details->total_cash - $register_details->total_cash_refund - $register_details->total_online_refund- $register_details->total_expense) > $request->register_close_amount) {
                $close_status = 'negative';
            }elseif(($register_details->open_amount + $register_details->total_cash - $register_details->total_cash_refund - $register_details->total_online_refund - $register_details->total_expense) == $request->register_close_amount) {
                $close_status = 'equal';
            }else {
               $close_status = 'equal'; 
            }
            switch($request->close_register) {

                case 'save-cash-on-hand':
                    $cashRegister->update([
                        'register_close_amount' => $request->register_close_amount,
                        'total_sales_amount' => $register_details->total_sale, //visa and cash
                        'close_status_amount' => abs($close_status_amount),
                        'close_status' => $close_status
                    ]);

                    return redirect(route('register.close', $cashRegister->id));
                break;

                case 'save-close-info':
                    $cashRegister->update([
                        'total_cash' => $request->total_cash,
                        'total_card_slips' => $request->total_card_slips,
                        'total_cheques' => $request->total_cheques ?? null,
                        'closing_note' => $request->closing_note,
                        'next_day_amount' => abs($cashRegister->register_close_amount - $request->closing_amount),
                        'closing_amount' => $request->closing_amount,
                        'closed_at' => Carbon::now()->toDateTimeString(),
                        'status' => 'close'
                    ]);
                    return redirect(route('sale.pos'));
                break;
            }
        } catch (\Exception $e) {
            dd($e);
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        
    }

    public function allCashRegisters() {

        $registers = cashRegister::latest()->paginate(20);

        return view('orders.all-registers', compact('registers'));
    }

    public function getRegisterShow(CashRegister $register)
    {
        $register_details =  $this->getRegisterTransactions($register->id);
        $user_id = auth()->user()->id;
        return view('orders.show-register')
                    ->with(compact('register_details', 'register'));
    }
}
