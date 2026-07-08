  <div class="main-header">
      <div class="main-header-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
              <a href="../index.html" class="logo">
                  <img {{-- src="../assets/img/kaiadmin/logo_light.svg" --}} alt="navbar brand" class="navbar-brand" />
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
          <!-- End Logo Header -->
      </div>
      <!-- Navbar Header -->
      <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
          <div class="container-fluid">
              <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">

              </nav>

              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">





                  <li class="nav-item topbar-user dropdown hidden-caret">
                      <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                          aria-expanded="false">
                          <div class="avatar-sm">
                              <img src="{{ asset('backend_assets/img/profile.jpg') }}" alt="..."
                                  class="avatar-img rounded-circle" />
                          </div>
                          <span class="profile-username">
                              <span class="op-7">Hi,</span>
                              <span class="fw-bold">{{ auth()->guard('parents')->user()->father_name ?? 'N/A' }}</span>
                          </span>
                      </a>
                      <ul class="dropdown-menu dropdown-user animated fadeIn">
                          <div class="dropdown-user-scroll scrollbar-outer">

                              <li class="nav-item">
                                  <form method="POST" action="{{ route('parent.logout') }}">
                                      @csrf
                                      <a class="nav-link">
                                          <button type="submit" class="nav-link btn btn-link p-0"
                                              style="display: flex; align-items: center;">
                                              <span class="nav-icon">
                                                  <i class="fa fa-sign-out "></i>
                                              </span>
                                              <span class="nav-text ms-2">Logout</span>
                                          </button>
                                      </a>
                                  </form>
                              </li>
                          </div>
                      </ul>
                  </li>
              </ul>
          </div>
      </nav>
      <!-- End Navbar -->
  </div>
