<?php $segment = Request::segment(2);?>

<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="" class="logo text-center w-100">
                <div class="logo">
                    <i class="fas fa-graduation-cap text-white fs-2"></i>
                    <span class="text-white fw-bold mt-1" style="font-size:16px; letter-spacing:1px;margin:2px;">
                       DPS Parent Portal
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
            <a href="{{route('parent.index')}}"
              class="{{ $segment == 'dashboard' ? 'active-link' : '' }}">
                <i class="fas fa-home text-white"></i>
                <p class="text-white">Dashboard</p>
            </a>
        </li>

       
         {{-- Students --}}
         <li class="nav-item {{ $segment == 'student' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#Student"
              class="{{ $segment == '' ? 'student' : 'collapsed' }}"
              aria-expanded="{{ $segment == '' ? 'true' : 'false' }}">
                <i class="fas fa-calendar-alt text-white"></i>
                <p class="text-white">Students</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'student' ? 'show' : '' }}"
                id="Student">
                <ul class="nav nav-collapse">
                     <li>
                        <a href="{{route('parent.student.index')}}"
                          class="{{ $segment == 'student' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'student' ? 'text-white' : '' }}">List All</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Fee Chalan --}}
          <li class="nav-item {{ $segment == 'challan' ? 'active' : '' }}">
            <a data-bs-toggle="collapse"
              href="#Challan"
              class="{{ $segment == '' ? 'challan' : 'collapsed' }}"
              aria-expanded="{{ $segment == '' ? 'true' : 'false' }}">
                <i class="fas fa-money-bill-wave text-white"></i>
                <p class="text-white">Fee Challan</p>
                <span class="caret"></span>
            </a>

            <div class="collapse {{ $segment == 'challan' ? 'show' : '' }}"
                id="Challan">
                <ul class="nav nav-collapse">
                     <li>
                        <a href="{{route('parent.fee-challan')}}"
                          class="{{ $segment == 'challan' ? 'active-sub' : '' }}">
                            <span class="sub-item {{ $segment == 'challan' ? 'text-white' : '' }}">List Challans</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <hr>
         <li class="nav-item">
            <form method="POST" action="{{route('parent.logout')}}">
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