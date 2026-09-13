<?php $segment = Request::segment(2);
$segment_three = Request::segment(3);
?>

<style>
    ul li{
        padding: 0 8px !important;
    }
</style>
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('admin.dashboard') }}" class="logo text-center w-100">
                <div class="logo">
                    <i class="fas fa-graduation-cap text-white fs-2"></i>
                    <span class="text-white fw-bold mt-1 ms-3" style="font-size:16px; letter-spacing:1px;margin:2px;">
                        DPS Admin
                    </span>
                </div>
            </a>

            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>

            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">

                <!-- Dashboard -->
                <li class="nav-item {{ $segment == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="{{ $segment == 'dashboard' ? 'active-link' : '' }}">
                        <i class="fas fa-home text-white"></i>
                        <p class="text-white">Dashboard</p>
                    </a>
                </li>

                {{-- Academic Session --}}

                <li class="nav-item {{ request()->routeIs('admin.academic-session.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#AcademicSession"
                        class="{{ request()->routeIs('admin.academic-session.*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->routeIs('admin.academic-session.*') ? 'true' : 'false' }}">
                        <i class="fas fa-calendar-alt text-white"></i>
                        <p class="text-white">Academic Session</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('admin.academic-session.*') ? 'show' : '' }}"
                        id="AcademicSession">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.academic-session.index') }}"
                                    class="{{ request()->routeIs('admin.academic-session.*') && !request()->routeIs('admin.academic-session.add') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.academic-session.*') && !request()->routeIs('admin.academic-session.add') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.academic-session.*') && !request()->routeIs('admin.academic-session.add') ? 'text-white' : '' }}">
                                        List All
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.academic-session.add') }}"
                                    class="{{ request()->routeIs('admin.academic-session.add') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.academic-session.add') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.academic-session.add') ? 'text-white' : '' }}">
                                        Create New
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <!-- Admissions -->
                <li class="nav-item {{ request()->is('admin/admission*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#admissionMenu"
                        class="{{ request()->is('admin/admission*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->is('admin/admission*') ? 'true' : 'false' }}">
                        <i class="fas fa-user-graduate text-white"></i>
                        <p class="text-white">Admission</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->is('admin/admission*') ? 'show' : '' }}" id="admissionMenu">
                        <ul class="nav nav-collapse">

                            {{-- List All (view, edit, index sab pe active) --}}
                            <li>
                                <a href="{{ route('admin.admission.index') }}"
                                    class="{{ request()->is('admin/admission*') && !request()->is('admin/admission/add*') && !request()->is('admin/add/admission*') ? 'active-sub' : '' }}"
                                    style="{{ request()->is('admin/admission*') && !request()->is('admin/admission/add*') && !request()->is('admin/add/admission*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->is('admin/admission*') && !request()->is('admin/admission/add*') && !request()->is('admin/add/admission*') ? 'text-white' : '' }}">
                                        List All
                                    </span>
                                </a>
                            </li>

                            {{-- Create New --}}
                            <li>
                                <a href="{{ route('admin.add.admission') }}"
                                    class="{{ request()->is('admin/admission/add*') || request()->is('admin/add/admission*') ? 'active-sub' : '' }}"
                                    style="{{ request()->is('admin/admission/add*') || request()->is('admin/add/admission*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->is('admin/admission/add*') || request()->is('admin/add/admission*') ? 'text-white' : '' }}">
                                        Create New
                                    </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                {{-- Voucher --}}
                <li class="nav-item {{ request()->routeIs('admin.voucher.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#Voucher"
                        class="{{ request()->routeIs('admin.voucher.*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->routeIs('admin.voucher.*') ? 'true' : 'false' }}">
                        <i class="fas fa-file-invoice-dollar text-white"></i>
                        <p class="text-white">Voucher</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('admin.voucher.*') ? 'show' : '' }}" id="Voucher">
                        <ul class="nav nav-collapse">

                            <li>
                                <a href="{{ route('admin.voucher.index') }}"
                                    class="{{ request()->routeIs('admin.voucher.*') && !request()->routeIs('admin.voucher.add') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.voucher.*') && !request()->routeIs('admin.voucher.add') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.voucher.*') && !request()->routeIs('admin.voucher.add') ? 'text-white' : '' }}">
                                        List All
                                    </span>
                                </a>
                            </li>

                            {{-- Create New --}}
                            <li>
                                <a href="{{ route('admin.voucher.add') }}"
                                    class="{{ request()->routeIs('admin.voucher.add') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.voucher.add') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.voucher.add') ? 'text-white' : '' }}">
                                        Create New
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Parents --}}
                <li
                    class="nav-item {{ request()->routeIs('admin.challan.*') || request()->routeIs('admin.parent.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#ParentsMenu"
                        class="{{ request()->routeIs('admin.challan.*') || request()->routeIs('admin.parent.*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->routeIs('admin.challan.*') || request()->routeIs('admin.parent.*') ? 'true' : 'false' }}">
                        <i class="fas fa-users text-white"></i>
                        <p class="text-white">Parents</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('admin.challan.*') || request()->routeIs('admin.parent.*') ? 'show' : '' }}"
                        id="ParentsMenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.challan.index') }}"
                                    class="{{ request()->routeIs('admin.challan.*') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.challan.*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.challan.*') ? 'text-white' : '' }}">
                                        List Challans
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.parent.show') }}"
                                    class="{{ request()->routeIs('admin.parent.*') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.parent.*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.parent.*') ? 'text-white' : '' }}">
                                        Reset Password
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Accounts --}}
                <li
                    class="nav-item {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#AccountManagement"
                        class="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.reports.*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
                        <i class="fas fa-wallet text-white"></i>
                        <p class="text-white">Accounts</p>
                        <span class="caret"></span>
                    </a>

                    <div class="collapse {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.reports.*') ? 'show' : '' }}"
                        id="AccountManagement">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('admin.categories.index') }}"
                                    class="{{ request()->routeIs('admin.categories.*') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.categories.*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.categories.*') ? 'text-white' : '' }}">
                                        List Categories
                                    </span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('admin.reports.print') }}"
                                    class="{{ request()->routeIs('admin.reports.*') ? 'active-sub' : '' }}"
                                    style="{{ request()->routeIs('admin.reports.*') ? 'background-color: rgba(255,255,255,0.12); border-radius: 6px; color: #fff;' : '' }}">
                                    <span
                                        class="sub-item {{ request()->routeIs('admin.reports.*') ? 'text-white' : '' }}">
                                        Generate Reports
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <hr>
                <!-- Dashboard -->
                <li class="nav-item {{ $segment == 'general-settings' ? 'active' : '' }}">
                    <a href="{{ route('admin.general-settings.index') }}"
                        class="{{ $segment == 'general-settings' ? 'active-link' : '' }}">
                        <i class="fas fa-gear text-white"></i>
                        <p class="text-white">General Setting</p>
                    </a>
                </li>

                <li class="nav-item">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <a class="nav-link">
                            <button type="submit" class="nav-link btn btn-link p-0"
                                style="display: flex; align-items: center;">
                                <span class="nav-icon">
                                    <i class="fa fa-sign-out text-white"></i>
                                </span>
                                <span class="nav-text text-white">Logout</span>
                            </button>
                        </a>
                    </form>
                </li>

            </ul>

        </div>
    </div>
</div>
