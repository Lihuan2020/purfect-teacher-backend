<div class="sidebar-container">
    <div class="sidemenu-container navbar-collapse collapse fixed-menu">
        <div id="remove-scroll" class="left-sidemenu">
            <ul class="sidemenu  page-header-fixed sidemenu-hover-submenu" data-keep-expanded="false"
                data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                <li class="sidebar-toggler-wrapper hide">
                    <div class="sidebar-toggler">
                        <span></span>
                    </div>
                </li>
                <li class="sidebar-user-panel">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{ asset('assets/img/dp.jpg') }}" class="img-circle user-img-circle"
                                 alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>超级管理员</p>
                            <a href="#"><i class="fa fa-circle user-online"></i><span class="txtOnline">
												Online</span></a>
                        </div>
                    </div>
                </li>

                <!--begin-->
                <li class="nav-item start">
                    <a href="{{ route('home') }}" class="nav-link nav-toggle">
                        <i class="material-icons">home</i>
                        <span class="title">切换学校</span>
                    </a>
                </li>
                @if(session('school.id'))
                <li class="nav-item">
                    <a href="{{ route('school_manager.school.view') }}" class="nav-link">
                        <i class="material-icons">business</i>
                        <span class="title">{{ session('school.name') }}</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('school_manager.timetable.manager',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                        <i class="material-icons">event</i>
                        <span class="title">课程表管理</span>
                    </a>
                </li>
                @endif

            </ul>
        </div>
    </div>
</div>
