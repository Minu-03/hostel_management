<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$role = $_SESSION['role'];
$pageTitle = 'Help & Support';
$activePage = 'help';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>Getting Started</h3></div>
    <ul class="help-list">
        <li>
            <h4>How do I log in?</h4>
            <p>Use your email and password on the login page. Admins are created by the system; students receive their credentials from the hostel admin.</p>
        </li>
        <li>
            <h4>What can I do as a student?</h4><?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$role = $_SESSION['role'];
$pageTitle = 'Help & Support';
$activePage = 'help';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Getting Started -->
<div class="card">
    <div class="card-header">
        <h3>Getting Started</h3>
    </div>
    <ul class="help-list">
        <li>
            <h4>How do I log in?</h4>
            <p>Use your email and password on the login page. Admins are created by the system; students receive their credentials from the hostel admin.</p>
        </li>
        <li>
            <h4>What can I do as a student?</h4>
            <p>Students can view their room allocation, check payment history, file complaints, and manage visitor check-ins/check-outs.</p>
        </li>
        <li>
            <h4>What can I do as an admin?</h4>
            <p>Admins can manage students, rooms, allocations, payments, complaints, visitors, and generate reports.</p>
        </li>
        <li>
            <h4>How do I change my password?</h4>
            <p>Students can change their password from the "My Profile" page. Admins should contact the system administrator.</p>
        </li>
    </ul>
</div>

<!-- Frequently Asked Questions -->
<div class="card">
    <div class="card-header">
        <h3>Frequently Asked Questions</h3>
    </div>
    <div class="faq-item">
        <h4>How are rooms allocated?</h4>
        <p>An admin allocates an available room to a student from the Allocations page. The room's occupancy is tracked automatically, and the student is marked as checked in.</p>
    </div>
    <div class="faq-item">
        <h4>How do I check out of the hostel?</h4>
        <p>An admin can check you out from the Students page, or vacate your allocation from the Allocations page. Your room becomes available for others.</p>
    </div>
    <div class="faq-item">
        <h4>How are payments recorded?</h4>
        <p>Admins record payments with a receipt number, payment type, method, and status. Students can view their payment history at any time.</p>
    </div>
    <div class="faq-item">
        <h4>What happens after I submit a complaint?</h4>
        <p>Your complaint is sent to the admin with a status of "Open". The admin reviews it, responds, and updates the status to In Progress, Resolved, or Rejected.</p>
    </div>
    <div class="faq-item">
        <h4>Can I check in multiple visitors?</h4>
        <p>Yes, you can check in multiple visitors. Each visitor must be checked out when they leave the hostel premises.</p>
    </div>
</div>

<!-- Role-based Guides -->
<?php if ($role === 'admin'): ?>
    <div class="card">
        <div class="card-header">
            <h3>Admin Quick Guide</h3>
        </div>
        <ul class="help-list">
            <li>
                <h4>1. Add Students</h4>
                <p>Go to Students &rarr; Add Student. Enter the student's details. A login account is created automatically with the default password "student123".</p>
            </li>
            <li>
                <h4>2. Add Rooms</h4>
                <p>Go to Rooms &rarr; Add Room. Set the room number, capacity, type, and monthly fee.</p>
            </li>
            <li>
                <h4>3. Allocate Rooms</h4>
                <p>Go to Allocations &rarr; Allocate Room. Select a student and an available room. The student is automatically checked in.</p>
            </li>
            <li>
                <h4>4. Record Payments</h4>
                <p>Go to Payments &rarr; Record Payment. Select the student, enter the amount and details. A receipt number is generated automatically.</p>
            </li>
            <li>
                <h4>5. Handle Complaints</h4>
                <p>Go to Complaints, click "Respond" on any complaint to provide a response and update its status.</p>
            </li>
            <li>
                <h4>6. View Reports</h4>
                <p>Go to Reports for a full summary of students, rooms, finances, complaints, and visitors. Use the Print button to generate a hard copy.</p>
            </li>
        </ul>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h3>Student Quick Guide</h3>
        </div>
        <ul class="help-list">
            <li>
                <h4>1. View Your Room</h4>
                <p>Your dashboard shows your current room allocation, including room number, block, and monthly fee.</p>
            </li>
            <li>
                <h4>2. Check Payments</h4>
                <p>Go to My Payments to see all your payment records, including paid and pending amounts.</p>
            </li>
            <li>
                <h4>3. File a Complaint</h4>
                <p>Go to My Complaints &rarr; New Complaint. Choose a category, priority, and describe your issue.</p>
            </li>
            <li>
                <h4>4. Manage Visitors</h4>
                <p>Go to My Visitors &rarr; Check-In Visitor to register a guest. Check them out when they leave.</p>
            </li>
            <li>
                <h4>5. Update Profile</h4>
                <p>Go to My Profile to update your contact details and change your password.</p>
            </li>
        </ul>
    </div>
<?php endif; ?>

<!-- Contact Support -->
<div class="card">
    <div class="card-header">
        <h3>Contact Support</h3>
    </div>
    <p class="support-text">If you need further assistance, please contact the hostel administration office:</p>
    <div class="contact-info">
        <p><strong>Email:</strong> admin@hostel.com</p>
        <p><strong>Phone:</strong> +000-000-0000</p>
        <p><strong>Office Hours:</strong> Monday to Friday, 9:00 AM - 5:00 PM</p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
            <p>Students can view their room allocation, check payment history, file complaints, and manage visitor check-ins/check-outs.</p>
        </li>
        <li>
            <h4>What can I do as an admin?</h4>
            <p>Admins can manage students, rooms, allocations, payments, complaints, visitors, and generate reports.</p>
        </li>
        <li>
            <h4>How do I change my password?</h4>
            <p>Students can change their password from the "My Profile" page. Admins should contact the system administrator.</p>
        </li>
    </ul>
</div>

<div class="card">
    <div class="card-header"><h3>Frequently Asked Questions</h3></div>
    <div class="faq-item">
        <h4>How are rooms allocated?</h4>
        <p>An admin allocates an available room to a student from the Allocations page. The room's occupancy is tracked automatically, and the student is marked as checked in.</p>
    </div>
    <div class="faq-item">
        <h4>How do I check out of the hostel?</h4>
        <p>An admin can check you out from the Students page, or vacate your allocation from the Allocations page. Your room becomes available for others.</p>
    </div>
    <div class="faq-item">
        <h4>How are payments recorded?</h4>
        <p>Admins record payments with a receipt number, payment type, method, and status. Students can view their payment history at any time.</p>
    </div>
    <div class="faq-item">
        <h4>What happens after I submit a complaint?</h4>
        <p>Your complaint is sent to the admin with a status of "Open". The admin reviews it, responds, and updates the status to In Progress, Resolved, or Rejected.</p>
    </div>
    <div class="faq-item">
        <h4>Can I check in multiple visitors?</h4>
        <p>Yes, you can check in multiple visitors. Each visitor must be checked out when they leave the hostel premises.</p>
    </div>
</div>

<?php if ($role === 'admin'): ?>
<div class="card">
    <div class="card-header"><h3>Admin Quick Guide</h3></div>
    <ul class="help-list">
        <li><h4>1. Add Students</h4><p>Go to Students &rarr; Add Student. Enter the student's details. A login account is created automatically with the default password "student123".</p></li>
        <li><h4>2. Add Rooms</h4><p>Go to Rooms &rarr; Add Room. Set the room number, capacity, type, and monthly fee.</p></li>
        <li><h4>3. Allocate Rooms</h4><p>Go to Allocations &rarr; Allocate Room. Select a student and an available room. The student is automatically checked in.</p></li>
        <li><h4>4. Record Payments</h4><p>Go to Payments &rarr; Record Payment. Select the student, enter the amount and details. A receipt number is generated automatically.</p></li>
        <li><h4>5. Handle Complaints</h4><p>Go to Complaints, click "Respond" on any complaint to provide a response and update its status.</p></li>
        <li><h4>6. View Reports</h4><p>Go to Reports for a full summary of students, rooms, finances, complaints, and visitors. Use the Print button to generate a hard copy.</p></li>
    </ul>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header"><h3>Student Quick Guide</h3></div>
    <ul class="help-list">
        <li><h4>1. View Your Room</h4><p>Your dashboard shows your current room allocation, including room number, block, and monthly fee.</p></li>
        <li><h4>2. Check Payments</h4><p>Go to My Payments to see all your payment records, including paid and pending amounts.</p></li>
        <li><h4>3. File a Complaint</h4><p>Go to My Complaints &rarr; New Complaint. Choose a category, priority, and describe your issue.</p></li>
        <li><h4>4. Manage Visitors</h4><p>Go to My Visitors &rarr; Check-In Visitor to register a guest. Check them out when they leave.</p></li>
        <li><h4>5. Update Profile</h4><p>Go to My Profile to update your contact details and change your password.</p></li>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>Contact Support</h3></div>
    <p style="color: var(--text-light); font-size: 14px;">If you need further assistance, please contact the hostel administration office:</p>
    <div style="margin-top: 16px;">
        <p style="font-size: 14px;"><strong>Email:</strong> admin@hostel.com</p>
        <p style="font-size: 14px;"><strong>Phone:</strong> +000-000-0000</p>
        <p style="font-size: 14px;"><strong>Office Hours:</strong> Monday to Friday, 9:00 AM - 5:00 PM</p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
