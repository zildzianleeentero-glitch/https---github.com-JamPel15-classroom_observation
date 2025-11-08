<?php
require_once '../auth/session-check.php';
if($_SESSION['role'] != 'edp') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $data = [
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'name' => $_POST['name'],
                    'role' => 'dean',
                    'department' => $_POST['department']
                ];
                if($user->create($data)) {
                    $_SESSION['success'] = "Dean account created successfully.";
                } else {
                    $_SESSION['error'] = "Failed to create dean account.";
                }
                break;

            case 'deactivate':
                if($user->updateStatus($_POST['user_id'], 'inactive')) {
                    $_SESSION['success'] = "Dean account deactivated successfully.";
                } else {
                    $_SESSION['error'] = "Failed to deactivate dean account.";
                }
                break;

            case 'activate':
                if($user->updateStatus($_POST['user_id'], 'active')) {
                    $_SESSION['success'] = "Dean account activated successfully.";
                } else {
                    $_SESSION['error'] = "Failed to activate dean account.";
                }
                break;
        }
        header("Location: users.php");
        exit();
    }
}

// Get list of deans
$deans = $user->getUsersByRole('dean');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Deans - AI Classroom Evaluation</title>
    <?php include '../includes/header.php'; ?>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Manage Evaluators</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeanModal">
                    <i class="fas fa-plus me-2"></i>Add New Dean
                </button>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                while($row = $deans->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="<?php echo $row['status'] == 'active' ? 'deactivate' : 'activate'; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo $row['status'] == 'active' ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $row['status'] == 'active' ? 'user-slash' : 'user-check'; ?>"></i>
                                                <?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Dean Modal -->
    <div class="modal fade" id="addDeanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Dean</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department" required>
                                <option value="">Select Department</option>
                                <?php
                                $departments = [
                                    'CTE' => 'College of Teacher Education',
                                    'CAS' => 'College of Arts and Sciences',
                                    'CCJE' => 'College of Criminal Justice Education',
                                    'CBM' => 'College of Business Management',
                                    'CCIS' => 'College of Computing and Information Sciences',
                                    'CTHM' => 'College of Tourism and Hospitality Management',
                                    'ELEM' => 'Elementary',
                                    'JHS' => 'Junior High School',
                                    'SHS' => 'Senior High School'
                                ];
                                foreach($departments as $key => $value):
                                ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>