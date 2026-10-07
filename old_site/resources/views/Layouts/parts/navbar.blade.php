  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-light-danger elevation-3" style="overflow: hidden;">
      <!-- Brand Logo -->
      <!-- <a href="{{ route('dashboard') }}" class="brand-link">
          <img src="{{ asset('assets') }}/dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
              class="brand-image img-circle elevation-3" style="opacity: .8">
          <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
      </a> -->
      <a href="{{ route('dashboard') }}" class="brand-link" style="padding:initial !important;background-color: white;">
          <img src="{{ asset('assets') }}/Picture1.svg" alt="AdminLTE Logo" class="brand-image"
              style="opacity: .8;max-height:80px;margin-left:0px;margin-right:0px;float:none;">
          <span class="brand-text font-weight-light">&nbsp;&nbsp;</span>
      </a>
      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar user (optional) -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  @php
                      $path = '';
                      if (Auth::user()->photo) {
                          $path = asset('uploads/user_photos/' . Auth::user()->photo);
                      } else {
                          $path = asset('assets/dist/img/user2-160x160.jpg');
                      }
                  @endphp
                  <img src="{{ $path }}" class="img-circle elevation-2" alt="User Image">
              </div>
              <div class="info">
                  <a href="#" class="d-block">{{ Auth::user()->name }}</a>
              </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
              <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                  data-accordion="false">
                  @php($sidebarMenu = App\Helpers\Theme::getMenu())

                  @foreach ($sidebarMenu as $key => $row)
                      @if (!isset($row['children']))
                          @continue(canViewAny($row))
                      @else
                          <?php $count = 0; ?>
                          @php($childrenCount = count($row['children']))
                          @foreach ($row['children'] as $j => $item)
                              @if (canViewAny($item))
                                  @if (!isset($item['children']) || empty($item['children']))
                                      <?php $count++;
                                      unset($row['children'][$j]); ?>
                                  @else
                                      @php($subitemCount = 0)
                                      @foreach ($item['children'] as $k => $subitem)
                                          @if (canViewAny($subitem))
                                              @if (!isset($subitem['children']) || empty($subitem['children']))
                                                  <?php $subitemCount++;
                                                  unset($row['children'][$j]['children'][$k]); ?>
                                              @endif
                                          @endif
                                      @endforeach

                                      @if (count($item['children']) == $subitemCount)
                                          <?php $count++;
                                          unset($row['children'][$j]); ?>
                                      @endif
                                  @endif
                              @endif
                          @endforeach
                          @if ($childrenCount == $count)
                              @continue
                          @endif
                      @endif
                      @include('components.menu', ['item' => $row])
                  @endforeach
                  {{-- <li class="nav-item">
                      <a href="{{ route('dataClean') }}" class="nav-link">
                          <i class="nav-icon fas fa-database"></i>
                          <p>Data Clean</p>
                      </a>
                  </li> --}}
              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>
