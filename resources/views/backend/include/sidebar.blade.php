<?php $segment = Request::segment(2);?>

<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('admin.dashboard') }}" class="logo text-center w-100">
                <div class="logo">
                    <i class="fas fa-graduation-cap text-white fs-2"></i>
                    <span class="text-white fw-bold mt-1" style="font-size:16px; letter-spacing:1px;margin:2px;">
                       DPS Admin Portal
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
            <a href="{{ route('admin.dashboard') }}"
              class="{{ $segment == 'dashboard' ? 'active-link' : '' }}">
                <i class="fas fa-home text-white"></i>
                <p class="text-white">Dashboard</p>
            </a>
        </li>

        {{-- Academic Session --}}
         <li class="nav-item {{ $segment == 'academic-session' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#AcademicSession"
              class="{{ $segment == 'academic-session' ? '' : 'collapsed' }}"
              aria-expanded="{{ $segment == 'academic-session' ? 'true' : 'false' }}">
                <i class="fas fa-calendar-alt text-white"></i>
                <p class="text-white">Academic Session</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'academic-session' ? 'show' : '' }}"
                id="AcademicSession">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('admin.academic-session.index') }}"
                          class="{{ $segment == 'academic-session' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'academic-session' ? 'text-white' : '' }}">List All</span>
                        </a>
                    </li>
                     <li>
                        <a href="{{ route('admin.academic-session.add') }}"
                          class="{{ $segment == 'academic-session' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'academic-session' ? 'text-white' : '' }}">Create New</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <!-- Admissions -->
        <li class="nav-item {{ $segment == 'admission' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#admissionMenu"
              class="{{ $segment == 'admission' ? '' : 'collapsed' }}"
              aria-expanded="{{ $segment == 'admission' ? 'true' : 'false' }}">
                <i class="fas fa-user-graduate text-white"></i>
                <p class="text-white">Admission</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'admission' ? 'show' : '' }}"
                id="admissionMenu">
                <ul class="nav nav-collapse">
                    <li>
                        <a href="{{ route('admin.admission.index') }}"
                          class="{{ $segment == 'admission' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'admission' ? 'text-white' : '' }}">List All</span>
                        </a>
                    </li>
                    <li>
                         <a href="{{ route('admin.add.admission') }}"
                          class="{{ $segment == 'admission' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'admission' ? 'text-white' : '' }}">Create New</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

         {{-- Voucher  --}}
         <li class="nav-item {{ $segment == 'voucher' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#Voucher"
              class="{{ $segment == '' ? 'voucher' : 'collapsed' }}"
              aria-expanded="{{ $segment == '' ? 'true' : 'false' }}">
                <i class="fas fa-calendar-alt text-white"></i>
                <p class="text-white">Voucher</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'voucher' ? 'show' : '' }}"
                id="Voucher">
                <ul class="nav nav-collapse">
                   
                     <li>
                        <a href="{{ route('admin.voucher.index') }}"
                          class="{{ $segment == 'voucher' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'voucher' ? 'text-white' : '' }}">List All</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.voucher.add') }}"
                          class="{{ $segment == 'voucher' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'voucher' ? 'text-white' : '' }}">Create New</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Challans --}}
         <li class="nav-item {{ $segment == 'parent' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#Challan"
              class="{{ $segment == '' ? 'parent' : 'collapsed' }}"
              aria-expanded="{{ $segment == '' ? 'true' : 'false' }}">
               <i class="fas fa-users text-white"></i>
                <p class="text-white">Parents</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'parent' ? 'show' : '' }}"
                id="Challan">
                <ul class="nav nav-collapse">
                   
                     <li>
                        <a href="{{ route('admin.challan.index') }}"
                          class="{{ $segment == 'parent' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'parent' ? 'text-white' : '' }}">List Challans</span>
                        </a>
                    </li>

                      <li>
                        <a href="{{route('admin.parent.show')}}"
                          class="{{ $segment == 'parent' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'parent' ? 'text-white' : '' }}">Reset Password</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Account Management --}}
        <li class="nav-item {{ $segment == 'account' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
            href="#AccountManagement"
            class="{{ $segment == '' ? 'account' : 'collapsed' }}"
            aria-expanded="{{ $segment == '' ? 'true' : 'false' }}">
            
                <i class="fas fa-wallet text-white"></i> {{-- icon change --}}
                <p class="text-white">Accounts</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'account' ? 'show' : '' }}"
                id="AccountManagement">
                <ul class="nav nav-collapse">
                    
                    <li>
                        <a href="{{route('admin.categories.index')}}"
                        class="{{ $segment == 'account' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'account' ? 'text-white' : '' }}">
                               List Categories
                            </span>
                        </a>
                    </li>

                     <li>
                        <a href="{{route('admin.reports.print')}}"
                        class="{{ $segment == 'account' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'account' ? 'text-white' : '' }}">
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
            <a href="{{route('admin.general-settings.index')}}"
              class="{{ $segment == 'general-settings' ? 'active-link' : '' }}">
                <i class="fas fa-gear text-white"></i>
                <p class="text-white">General Setting</p>
            </a>
        </li>

         <li class="nav-item">
            <form method="POST" action="{{ route('admin.logout') }}" >
                @csrf
                <a class="nav-link">
                    <button type="submit" class="nav-link btn btn-link p-0" style="display: flex; align-items: center;">
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