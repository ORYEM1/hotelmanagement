<!-- Beginning of Navigation -->
<nav id="menu">
    <div id='cssmenu'>
        <ul>
            <?php
            $menu = array();

           // $menu['Dashboard'] = array('href' => '/', 'icon_class' => 'fa fa-dashboard');

            $menu['Reservations'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Reservations']['submenu']['New Reservation'] = array('href' => '/new_reservation');
            $menu['Reservations']['submenu']['Manage Reservations'] = array('href' => '/manage_reservations');
            $menu['Reservations']['submenu']['Reservation History'] = array('href' => '/reservation_history');
            $menu['Reservations']['submenu']['Guest List'] = array('href' => '/guest_list');
            $menu['Reservations']['submenu']['Guest Reviews'] = array('href' => '/guest_reviews');
           // $menu['Guests'] = array('href' => '#', 'class' => 'has-sub');


            $menu['Rooms'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Rooms']['submenu']['Available Rooms'] = array('href' => '/available_rooms');
            $menu['Rooms']['submenu']['Room Categories'] = array('href' => '/room_categories');
            $menu['Rooms']['submenu']['Room Pricing'] = array('href' => '/room_pricing');
            $menu['Rooms']['submenu']['Maintenance Requests'] = array('href' => '/maintenance_requests');



            $menu['Billing & Payments'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Billing & Payments']['submenu']['Invoices'] = array('href' => '/invoices');
            $menu['Billing & Payments']['submenu']['Payment Methods'] = array('href' => '/payment_methods');
            $menu['Billing & Payments']['submenu']['Transaction History'] = array('href' => '/transaction_history');



           /* $menu['Stock Management'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Stock Management']['submenu']['Inventory'] = array('href' => '/inventory');
            $menu['Stock Management']['submenu']['Suppliers'] = array('href' => '/suppliers');
            $menu['Stock Management']['submenu']['Stock Reports'] = array('href' => '/stock_reports');*/



            //$menu['Staff Management'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Admin Menu'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Admin Menu']['submenu']['Staff List'] = array('href' => '/staff_list');
            $menu['Admin Menu']['submenu']['Roles & Permissions'] = array('href' => '/roles_permissions');
            $menu['Admin Menu']['submenu']['Attendance'] = array('href' => '/staff_attendance');
            $menu['Admin Menu']['submenu']['Users'] = array('href' => '/users');
            $menu['Admin Menu']['submenu']['User Roles'] = array('href' => '/roles');
            $menu['Admin Menu']['submenu']['System Logs'] = array('href' => '/system_logs');

            $menu['Reports & Analytics'] = array('href' => '#', 'class' => 'has-sub');
            $menu['Reports & Analytics']['submenu']['Revenue Reports'] = array('href' => '/revenue_reports');
            $menu['Reports & Analytics']['submenu']['Guest Reports'] = array('href' => '/guest_reports');
            $menu['Reports & Analytics']['submenu']['Booking Trends'] = array('href' => '/booking_trends');

            $menu['My Account'] = array('href' => '#', 'class' => 'has-sub', 'icon_class' => 'fa fa-user', 'id' => 'my_account');
            //$menu['My Account']['submenu']["Logged in as {$_SESSION['user_data']['first_name']}"] = array('href' => '#');
            $menu['My Account']['submenu']['Change Theme'] = array('href' => '/users/change_theme', 'class' => 'open_modal');
            $menu['My Account']['submenu']['Change Password'] = array('href' => '/users/change_password', 'class' => 'open_modal');
            $menu['My Account']['submenu']['Logout'] = array('href' => '/logout');

            echo generate_menu($menu);
            ?>
        </ul>
    </div>
</nav>
<!-- End of Navigation -->
