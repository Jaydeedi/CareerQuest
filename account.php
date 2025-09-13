<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Quest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/all.min.css">
<link href="css/poppins.css" rel="stylesheet">
<script src="js/jquery-3.6.0.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/chart.min.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #dbeafe;
            --secondary: #f59e0b;
            --secondary-light: #fef3c7;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --white: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navigation Styles */
        .navbar {
            background-color: var(--white);
            box-shadow: var(--box-shadow);
            padding: 12px 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--primary);
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        
        .nav-link {
            color: var(--dark);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .nav-link.active {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .nav-link i {
            margin-right: 8px;
        }
        
        /* Main Content */
        .main-content {
            padding: 30px 0;
            flex: 1;
        }
        
        .page-header {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            border-left: 5px solid var(--primary);
        }
        
        .page-header h1 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: var(--gray);
            margin-bottom: 0;
        }
        
        /* Account Settings Cards */
        .account-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .account-card-header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        
        .account-card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .account-card-header i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .account-card-body {
            padding: 25px;
        }
        
        /* Profile image */
        .profile-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--primary-light);
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-image-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 600;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-upload-btn {
            background-color: var(--primary-light);
            color: var(--primary);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .profile-upload-btn:hover {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .profile-upload-btn i {
            margin-right: 8px;
        }
        
        /* Form styles */
        .form-label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: var(--border-radius);
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .form-text {
            color: var(--gray);
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Security settings */
        .security-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .security-item:last-child {
            border-bottom: none;
        }
        
        .security-item-info h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .security-item-info p {
            margin: 0;
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Activity log styles */
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .activity-icon.login {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        
        .activity-icon.password {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .activity-icon.update {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .activity-info h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .activity-info p {
            margin: 0;
            color: var(--gray);
            font-size: 14px;
        }
        
        .activity-timestamp {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-danger:hover, .btn-danger:focus {
            background-color: #b91c1c;
            border-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-secondary {
            border-color: #e2e8f0;
            color: var(--gray);
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: #f1f5f9;
            color: var(--dark);
        }
        
        /* Verification badge */
        .verification-badge {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        
        .verification-badge i {
            margin-right: 5px;
        }
        
        /* Footer */
        .footer {
            background-color: var(--white);
            padding: 20px 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            margin-top: auto;
        }
        
        .footer p {
            margin: 0;
            color: var(--gray);
            font-size: 14px;
            text-align: center;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
            }
            
            .account-card-header, .account-card-body {
                padding: 15px;
            }
            
            .profile-image, .profile-image-placeholder {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.html">
                <span>Career Quest</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link active" href="account.html"><i class="fas fa-user-cog"></i> Manage Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="main-content">
        <div class="container">
            
            <div class="page-header">
                <h1>Account Settings</h1>
                <p>Manage your profile, security settings, and account preferences</p>
            </div>

            <div class="row">
                
                <div class="col-lg-8">
                    
                    <div class="account-card">
                        <div class="account-card-header">
                            <i class="fas fa-user-circle"></i>
                            <h5>Profile Information</h5>
                        </div>
                        <div class="account-card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="profile-image-container">
                                        <div class="profile-image-placeholder">
                                            J
                                        </div>
                                        <button class="profile-upload-btn">
                                            <i class="fas fa-camera"></i> Change Photo
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <form id="profileForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" value="Juan" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" value="Dela Cruz" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="studentId" class="form-label">Student ID</label>
                                            <input type="text" class="form-control" id="studentId" value="1234567890" readonly>
                                            <div class="form-text">Your student ID cannot be changed.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" value="09123456789">
                                        </div>
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="department" value="Information Technology" readonly>
                                            <div class="form-text">Your department is assigned by the system administrator.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="yearLevel" class="form-label">Year Level</label>
                                            <select class="form-select" id="yearLevel">
                                                <option value="1">First Year</option>
                                                <option value="2">Second Year</option>
                                                <option value="3" selected>Third Year</option>
                                                <option value="4">Fourth Year</option>
                                                <option value="5">Fifth Year</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-outline-secondary me-2">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                
                <div class="col-lg-4">
                    
                    <div class="account-card">
                        <div class="account-card-header">
                            <i class="fas fa-info-circle"></i>
                            <h5>Account Summary</h5>
                        </div>
                        <div class="account-card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Account Status</span>
                                    <span class="badge bg-success">Active</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Account Type</span>
                                    <span>Student</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Member Since</span>
                                    <span>August 15, 1935</span>
                                </li>
                            </ul>
                        </div>
                    </div>



                    
                    <div class="account-card">
                        <div class="account-card-header">
                            <i class="fas fa-cogs"></i>
                            <h5>Account Actions</h5>
                        </div>
                        <div class="account-card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateAccountModal">
                                    <i class="fas fa-user-slash me-2"></i> Deactivate Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <div class="form-text">
                                Password must be at least 8 characters long and include uppercase letters, lowercase letters, numbers, and special characters.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePasswordBtn">Change Password</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="loginHistoryModal" tabindex="-1" aria-labelledby="loginHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginHistoryModalLabel">Login History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Feb 27, 2024, 10:30 AM</td>
                                    <td>Windows PC</td>
                                    <td>Chrome 121.0.6167.140</td>
                                    <td>192.168.1.1</td>
                                    <td><span class="badge bg-success">Successful</span></td>
                                </tr>
                                <tr>
                                    <td>Feb 23, 2024, 5:30 PM</td>
                                    <td>Windows PC</td>
                                    <td>Chrome 121.0.6167.140</td>
                                    <td>192.168.1.1</td>
                                    <td><span class="badge bg-danger">Failed</span></td>
                                </tr>
                                <tr>
                                    <td>Feb 22, 2024, 11:20 AM</td>
                                    <td>MacOS</td>
                                    <td>Safari 17.2</td>
                                    <td>192.168.1.3</td>
                                    <td><span class="badge bg-success">Successful</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Export History
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deactivateAccountModal" tabindex="-1" aria-labelledby="deactivateAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deactivateAccountModalLabel">Deactivate Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Deactivating your account will remove your access to the Faculty Evaluation System.
                    </div>
                    <p>Please understand the following consequences:</p>
                    <ul>
                        <li>You will no longer be able to log in to the system</li>
                        <li>Your pending evaluations will be cancelled</li>
                        <li>Your profile information will be preserved for record-keeping purposes</li>
                        <li>To reactivate your account, you will need to contact the system administrator</li>
                    </ul>
                    <div class="mb-3">
                        <label for="deactivateReason" class="form-label">Please tell us why you're leaving (optional):</label>
                        <textarea class="form-control" id="deactivateReason" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="deactivatePassword" class="form-label">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="deactivatePassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeactivateBtn">
                        <i class="fas fa-user-slash me-2"></i>Deactivate Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#profileForm').submit(function(e) {
                e.preventDefault();
                
                const saveBtn = $(this).find('button[type="submit"]');
                const originalText = saveBtn.html();
                saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                saveBtn.prop('disabled', true);
                
                setTimeout(function() {
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                    
                    const successAlert = $('<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">' +
                                         '<i class="fas fa-check-circle me-2"></i> Profile updated successfully!' +
                                         '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                         '</div>');
                    $('#profileForm').prepend(successAlert);
                    
                    setTimeout(function() {
                        successAlert.alert('close');
                    }, 3000);
                }, 1500);
            });
            
            $('#savePasswordBtn').click(function() {
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();

                if (!currentPassword || !newPassword || !confirmPassword) {
                    alert('Please fill out all password fields.');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    alert('New password and confirmation do not match.');
                    return;
                }
                
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                if (!passwordRegex.test(newPassword)) {
                    alert('Password does not meet the security requirements.');
                    return;
                }
                
                const saveBtn = $(this);
                const originalText = saveBtn.html();
                saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Changing...');
                saveBtn.prop('disabled', true);
                
                setTimeout(function() {
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                    
                    $('#changePasswordModal').modal('hide');
                    
                    const successToast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">' +
                                         '<div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">' +
                                         '<div class="toast-header bg-success text-white">' +
                                         '<strong class="me-auto">Success</strong>' +
                                         '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>' +
                                         '</div>' +
                                         '<div class="toast-body">' +
                                         'Your password has been changed successfully.' +
                                         '</div>' +
                                         '</div>' +
                                         '</div>');
                    $('body').append(successToast);
                    
                    $('#changePasswordForm')[0].reset();
                    
                    setTimeout(function() {
                        successToast.remove();
                    }, 3000);
                }, 1500);
            });
            
            $('#downloadDataBtn').click(function() {
                const format = $('#downloadFormat').val();
                

                const downloadBtn = $(this);
                const originalText = downloadBtn.html();
                downloadBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Preparing...');
                downloadBtn.prop('disabled', true);
                
                
                setTimeout(function() {
                    downloadBtn.html(originalText);
                    downloadBtn.prop('disabled', false);
                    

                    $('#downloadDataModal').modal('hide');
                    

                    alert(`Your data is being prepared for download in ${format.toUpperCase()} format. The download will start shortly.`);
                }, 1500);
            });
            
           
            $('#confirmDeactivateBtn').click(function() {
                const password = $('#deactivatePassword').val();
                
                if (!password) {
                    alert('Please enter your password to confirm deactivation.');
                    return;
                }
                

                const deactivateBtn = $(this);
                const originalText = deactivateBtn.html();
                deactivateBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                deactivateBtn.prop('disabled', true);
                

                setTimeout(function() {
                    deactivateBtn.html(originalText);
                    deactivateBtn.prop('disabled', false);
                    
                    alert('Your account has been scheduled for deactivation. You will be logged out now.');
                    
                    window.location.href = 'index.php';
                }, 2000);
            });
        });
    </script>
</body>
</html></td>