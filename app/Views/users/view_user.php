<?php
echo view('page_heading')
?>
<!DOCTYPE html>
<table class ="table table-bordered table-striped table-hover table-condensed">
    <tr><td>ID</td><td><?php echo $record['id']?></td></tr>
    <tr><td>First Name</td><td><?php echo $record['first_name']?></td></tr>
    <tr><td>Last Name</td><td><?php echo $record['last_name']?></td></tr>
    <tr><td>Email</td><td><?php echo $record['email']?></td></tr>
    <tr><td>Username</td><td><?php echo $record['username']?></td></tr>
    <tr><td>Phone Number</td><td><?php echo $record['phone_number']?></td></tr>
    <tr><td>Gender</td><td><?php echo $record['gender']?></td></tr>


</table>
