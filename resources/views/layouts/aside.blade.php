@php $isEmbedded = determineIfAppIsEmbedded() @endphp

<aside id="sidebar" class="sidebar" @if ($isEmbedded) style="background-color:#f1f2f4" @endif>

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item collapsed">
            <a class="nav-link " href="{{ route('home') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        @if (auth()->user()->role_id == 1 ||
                auth()->user()->role_id == 3 ||
                auth()->user()->role_id == 4 ||
                auth()->user()->role_id == 6)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#operation" data-bs-toggle="collapse" aria-expanded="false"
                    href="#">
                    <i class="bi bi-menu-button-wide"></i><span>Operation</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="operation" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    @if (auth()->user()->role_id != 6)
                        <li>
                            <a id="sync" href="{{ route('shopify.orders') }}">
                                <i class="bi bi-circle"></i><span>Sync & Assign Orders</span>
                            </a>
                        </li>
                        <li>
                            <a id="pending" href="{{ route('shopify.pending_payment') }}">
                                <i class="bi bi-circle"></i><span>Fawry Pending Orders</span>
                            </a>
                        </li>
                        <li>
                            <a id="products" href="{{ route('shopify.products') }}">
                                <i class="bi bi-circle"></i><span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a id="variants" href="{{ route('shopify.product_variants') }}">
                                <i class="bi bi-circle"></i><span>Product Variants</span>
                            </a>
                        </li>
                        <li>
                            <a id="product_warehouse" href="{{ route('shopify.product_warehouse') }}">
                                <i class="bi bi-circle"></i><span>Warehouse Products</span>
                            </a>
                        </li>
                        <li>
                            <a id="locations" href="{{ route('shopify.locations') }}">
                                <i class="bi bi-circle"></i><span>Locations</span>
                            </a>
                        </li>
                        <li>
                            <a id="customers" href="{{ route('shopify.customers') }}">
                                <i class="bi bi-circle"></i><span>Customers</span>
                            </a>
                        </li>
                    @endif

                    <li>
                        <a id="prepares" href="{{ route('prepare.all') }}">
                            <i class="bi bi-circle"></i><span>All Orders</span>
                        </a>
                    </li>
                    <li>
                        <a id="sales" href="{{ route('sales.all') }}">
                            <i class="bi bi-circle"></i><span>All Sales</span>
                        </a>
                    </li>
                    @if (auth()->user()->role_id != 6)
                        <li>
                            <a id="reviewed" href="{{ route('prepare.reviewed') }}">
                                <i class="bi bi-circle"></i><span>Ready To Ship</span>
                            </a>
                        </li>
                    @endif


                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 5)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#preparation" data-bs-toggle="collapse"
                    aria-expanded="false" href="#">
                    <i class="bi bi-box"></i><span>Preparation</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="preparation" class="nav-content collapse" data-bs-parent="#sidebar-nav">

                    <li>
                        <a id="new" href="{{ route('prepare.new') }}">
                            <i class="bi bi-circle"></i><span>New Orders</span>
                        </a>
                    </li>
                    <li>
                        <a id="hold" href="{{ route('prepare.hold') }}">
                            <i class="bi bi-circle"></i><span>Hold Orders</span>
                        </a>
                    </li>
                    <li>
                        <a id="prepare" href="{{ route('prepare.order') }}">
                            <i class="bi bi-circle"></i><span>Prepared Orders</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#pickups" data-bs-toggle="collapse" aria-expanded="false"
                    href="#">
                    <i class="bi bi-files"></i><span>Pickups</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="pickups" class="nav-content collapse" data-bs-parent="#sidebar-nav">

                    <li class="nav-item">
                        <a id="index" class="nav-link collapsed" href="{{ route('pickups.index') }}">
                            <i class="bi bi-files"></i>
                            <span>Daily Pickups</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="" class="nav-link collapsed" href="{{ route('return-pickups.index') }}">
                            <i class="bi bi-files"></i>
                            <span>Returns Pickups</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 6)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#edited" data-bs-toggle="collapse" aria-expanded="false"
                    href="#">
                    <i class="bi bi-arrow-repeat"></i><span>Edited Orders</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="edited" class="nav-content collapse" data-bs-parent="#sidebar-nav">

                    <li class="nav-item">
                        <a id="resync" class="nav-link collapsed" href="#" onclick="resync()">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Re-Sync Order</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="resynced" class="nav-link collapsed" href="{{ route('prepare.resynced-orders') }}">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Re-Synced Orders</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="returned" class="nav-link collapsed" href="{{ route('orders.returned') }}">
                            <i class="bi bi-arrow-return-right"></i>
                            <span>Returned Orders</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 6)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#warehouse" data-bs-toggle="collapse"
                    aria-expanded="false" href="#">
                    <i class="bi bi-house"></i></i><span>Warehouse Products</span><i
                        class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="warehouse" class="nav-content collapse" data-bs-parent="#sidebar-nav">

                    <li class="nav-item">
                        <a id="add" class="nav-link collapsed" href="#" onclick="warehouse()">
                            <i class="bi bi-house"></i>
                            <span>Add Warehouse Transfer</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="transfers"class="nav-link collapsed" href="{{ route('inventories.index') }}">
                            <i class="bi bi-folder-minus"></i>
                            <span>Warehouse Transfers</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#reports" data-bs-toggle="collapse"
                    aria-expanded="false" href="#">
                    <i class="bi bi-folder-minus"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="reports" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li class="nav-item">
                        <a id="staff"class="nav-link collapsed" href="{{ route('reports.staff') }}">
                            <i class="bi bi-folder-minus"></i>
                            <span>Staff Report</span>
                        </a>
                    </li>
                    <li>
                        <a id="cancelled" class="nav-link collapsed" href="{{ route('prepare.cancelled-orders') }}">
                            <i class="bi bi-circle"></i><span>Cancelled Orders Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="returned_report" class="nav-link collapsed" href="{{ route('reports.returned') }}">
                            <i class="bi bi-arrow-return-right"></i>
                            <span>Returns Report</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a id="returned_products" class="nav-link collapsed"
                            href="{{ route('products.returned') }}">
                            <i class="bi bi-arrow-return-right"></i>
                            <span>Returned Products Report</span>
                        </a>
                    </li>
                    <li>
                        <a id="hold_products" class="nav-link collapsed"
                            href="{{ route('prepare.hold-products') }}">
                            <i class="bi bi-circle"></i><span>Hold Products Report</span>
                        </a>
                    </li>
                    <li>
                        <a id="stock" class="nav-link collapsed" href="{{ route('reports.stock') }}">
                            <i class="bi bi-circle"></i><span>Stock Report</span>
                        </a>
                    </li>
                    <li>
                        <a id="registers" class="nav-link collapsed" href="{{ route('reports.cash_registers') }}">
                            <i class="bi bi-circle"></i><span>Cash Registers Report</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Components Nav -->
        @endif
        @if (Auth::user()->getShopifyStore->isPublic())
            @canany(['all-access', 'write-members', 'read-members'])
                <li class="nav-item" id="team">
                    <a class="nav-link collapsed" href="{{ route('members.index') }}">
                        <i class="bi bi-people"></i>
                        <span>My Team</span>
                    </a>
                </li><!-- End Contact Page Nav -->
            @endcanany
        @else
        @endif






        <li class="nav-item">
            <a class="nav-link collapsed"
                onclick="event.preventDefault(); document.getElementById('logout-user').submit();">
                <i class="bi bi-box-arrow-right"></i>
                <form id="logout-user" action="{{ route('logout') }}" method="POST" class="d-none"
                    style="display: none">
                    @csrf
                </form>
                <span>Sign Out</span>
            </a>
        </li><!-- End Blank Page Nav -->

    </ul>

</aside>
<div class="modal fade" id="resync-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Re-Sync Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fulfillment_form">
                <form action="{{ route('orders.resync') }}" class="row g-3" method="POST">
                    @csrf

                    <div class="col-md-6">
                        <label for="reason">Edit Reason</label>
                        <select class="form-select aiz-selectpicker" name="reason"
                            data-minimum-results-for-search="Infinity" required>
                            <option value=""selected>Select</option>
                            <option value="Add Item">Add Item</option>
                            <option value="Remove Item">Remove Item</option>
                            <option value="Replace Item">Replace Item</option>
                            <option value="Update Qty">Update Qty</option>
                            <option value="OTHER">Other</option>

                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="order_id">Order ID</label>
                        <input type="text" name="order_id" class="form-control"
                            placeholder="Enter Order id and Hit Enter" required>
                    </div>
                    <div class="col-4 justify-content-center">
                        <button type="submit" class="btn btn-info">Re-Sync</button>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="inventory-modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Warehouse Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fulfillment_form">
                <form action="{{ route('inventory.import') }}" class="row g-3" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="col-md-6">
                        <label for="file">File</label>
                        <input type="file" name="sheet" class="form-control" placeholder="Upload file"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label for="reason">Choose Location</label>
                        <select class="form-select aiz-selectpicker" name="location_id"
                            data-minimum-results-for-search="Infinity" required>
                            <option value=""selected>Select</option>
                            @foreach ($locations as $key => $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach

                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="note">Note</label>
                        <textarea name="note" class="form-control" placeholder="Add Transfer Note"></textarea>
                    </div>
                    <div class="col-4 justify-content-center">
                        <button type="submit" class="btn btn-info">Upload</button>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
