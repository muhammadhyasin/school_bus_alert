<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="/images/favicon.ico">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        

        <!-- jquery.vectormap css -->
        <link href="/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

        <!-- DataTables -->
        <link href="/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />  

        <!-- Bootstrap Css -->
        <link href="/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
        <style>
            .noti-dot {
                position: absolute;
                top: 10px;
                right: 10px;
                display: none;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #f46a6a;
            }
            
            .notification-item {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #f6f6f6;
            }
            
            .notification-item:hover {
                background-color: #f8f9fa;
            }
            
            .notification-item:last-child {
                border-bottom: none;
            }
            </style>
        

    </head>

    <body>
    
    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

        <!-- Begin page -->
        <div id="layout-wrapper">

            
            <header id="page-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box">
                            <a href="" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="/images/logo-sm.png" alt="logo-sm" height="30">
                                </span>
                                <span class="logo-lg">
                                    <img src="/images/logo-light.png" alt="logo-dark" height="50">
                                </span>
                            </a>

                            <a href="" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="/images/logo-sm.png" alt="logo-sm-light" height="30">
                                </span>
                                <span class="logo-lg">
                                    <img src="/images/logo-light.png" alt="logo-light" height="50">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                            <i class="ri-menu-2-line align-middle"></i>
                        </button>

                        <!-- App Search-->
                        <form class="app-search d-none d-lg-block">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="ri-search-line"></span>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex">
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                                  data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-notification-3-line"></i>
                                <span class="noti-dot"></span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0"> Notifications </h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="javascript:void(0)" class="small mark-all-read"> Mark all as read</a>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebarr >
                                    <!-- Notifications will be dynamically inserted here -->
                                </div>
                                <div class="p-2 border-top">
                                    <div class="d-grid">
                                        <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                                            <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown d-inline-block user-dropdown">
                            <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="rounded-circle header-profile-user" src="/images/users/avatar-1.png"
                                    alt="Header Avatar">
                                <span class="d-none d-xl-inline-block ms-1">{{ Auth::user()->name }}</span>
                                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a class="dropdown-item" href="#"><i class="ri-user-line align-middle me-1"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="ri-shut-down-line align-middle me-1 text-danger"></i> Logout
                                </a>

                                
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                                <i class="ri-settings-2-line"></i>
                            </button>
                        </div>
            
                    </div>
                </div>
            </header>

            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">

                <div data-simplebar class="h-100">

                    <!-- User details -->
                    <div class="user-profile text-center mt-3">
                        <div class="">
                            <img src="/images/users/avatar-1.png" alt="" class="avatar-md rounded-circle">
                        </div>
                        <div class="mt-3">
                            <h4 class="font-size-16 mb-1">{{ Auth::user()->name }}</h4>
                            <span class="text-muted"><i class="ri-record-circle-line align-middle font-size-14 text-success"></i> Online</span>
                        </div>
                    </div>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">
                        <!-- Left Menu Start -->
                        <ul class="metismenu list-unstyled" id="side-menu">
                            <li class="menu-title">Menu</li>

                            <li>
                                <a href="" class="waves-effect">
                                    <i class="ri-dashboard-line"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- Sidebar -->
                </div>
            </div>
            <!-- Left Sidebar End -->

            

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                    @yield('content')

                    </div>
                        <footer class="footer">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <script>document.write(new Date().getFullYear())</script> © Project.
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-sm-end d-none d-sm-block">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        
                    </div>
                    <!-- end main content-->
        
                </div>
                <!-- END layout-wrapper -->
        
                <!-- Right Sidebar -->
                <div class="right-bar">
                    <div data-simplebar class="h-100">
                        <div class="rightbar-title d-flex align-items-center px-3 py-4">
                    
                            <h5 class="m-0 me-2">Settings</h5>
        
                            <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                                <i class="mdi mdi-close noti-icon"></i>
                            </a>
                        </div>
        
                        <!-- Settings -->
                        <hr class="mt-0" />
                        <h6 class="text-center mb-0">Choose Layouts</h6>
        
                        <div class="p-4">
                            
        
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                                <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                            </div>
            
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch" data-bsStyle="/css/bootstrap-dark.min.css" data-appStyle="/css/app-dark.min.css">
                                <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                            </div>
            
                        </div>
        
                    </div> <!-- end slimscroll-menu-->
                </div>
                <!-- /Right-bar -->
        
                <!-- Right bar overlay-->
                <div class="rightbar-overlay"></div>
        
                <!-- JAVASCRIPT -->
                <script src="/libs/jquery/jquery.min.js"></script>
                <script src="/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="/libs/metismenu/metisMenu.min.js"></script>
                <script src="/libs/simplebar/simplebar.min.js"></script>
                <script src="/libs/node-waves/waves.min.js"></script>
        
                
                <!-- apexcharts -->
                <script src="/libs/apexcharts/apexcharts.min.js"></script>
        
                <!-- jquery.vectormap map -->
                <script src="/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
                <script src="/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-us-merc-en.js"></script>
        
                <!-- Required datatable js -->
                <script src="/libs/datatables.net/js/jquery.dataTables.min.js"></script>
                <script src="/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
                
                <!-- Responsive examples -->
                <script src="/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
                <script src="/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
        
                <script src="/js/pages/dashboard.init.js"></script>
        
                <!-- App js -->
                <script src="/js/app.js"></script>
                @stack('scripts')
            </body>
        
        </html>