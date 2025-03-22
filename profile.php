<?php
$pageTitle = 'My Profile';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ensure user is logged in
requireLogin();

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    $_SESSION['message'] = 'An error occurred. Please try again.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Update profile if form submitted
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $height = (float)($_POST['height'] ?? 0);
    $weight = (float)($_POST['weight'] ?? 0);
    
    // Update user profile
    if (updateUserProfile($userId, $firstName, $lastName, $age, $gender, $height, $weight)) {
        $success = 'Profile updated successfully.';
        // Refresh user data
        $user = getUserById($userId);
    } else {
        $error = 'An error occurred while updating your profile.';
    }
}

// Change password if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Get form data
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All password fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } else {
        // Change password
        if (changeUserPassword($userId, $currentPassword, $newPassword)) {
            $success = 'Password changed successfully.';
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}

// Get user challenges
$userChallenges = getUserChallenges($userId);

// Get user's BMI if height and weight are available
$bmi = 0;
$bmiCategory = '';
if (!empty($user['height']) && !empty($user['weight'])) {
    $heightInMeters = $user['height'] / 100; // Convert cm to meters
    $bmi = calculateBMI($user['weight'], $heightInMeters);
    $bmiCategory = getBMICategory($bmi);
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="profile-header">
            <h2 class="mb-3">
                <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($user['username']); ?>
            </h2>
            <p><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-calendar-alt me-2"></i> Member since <?php echo formatDate($user['created_at']); ?></p>
            
            <?php if ($bmi > 0): ?>
                <div class="mt-4">
                    <h5>Your BMI: <?php echo $bmi; ?></h5>
                    <p class="mb-1">Category: <span class="badge bg-<?php echo $bmiCategory === 'Normal weight' ? 'success' : 'warning'; ?>"><?php echo $bmiCategory; ?></span></p>
                    <div class="progress" style="height: 0.5rem;">
                        <div class="progress-bar bg-<?php 
                            if ($bmi < 18.5) echo 'info';
                            elseif ($bmi < 25) echo 'success';
                            elseif ($bmi < 30) echo 'warning';
                            else echo 'danger';
                        ?>" role="progressbar" style="width: <?php echo min(($bmi / 40) * 100, 100); ?>%" aria-valuenow="<?php echo $bmi; ?>" aria-valuemin="0" aria-valuemax="40"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>Underweight</small>
                        <small>Normal</small>
                        <small>Overweight</small>
                        <small>Obese</small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Active Challenges</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($userChallenges)): ?>
                    <div class="p-3 text-center">
                        <p class="mb-0">You haven't joined any challenges yet.</p>
                        <a href="challenges.php" class="btn btn-sm btn-outline-primary mt-2">Browse Challenges</a>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($userChallenges as $index => $challenge): ?>
                            <?php if ($index < 3): ?>
                                <li class="list-group-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($challenge['name']); ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Progress:</small>
                                        <span class="badge bg-<?php 
                                            echo $challenge['status'] === 'completed' ? 'success' : 
                                                ($challenge['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                        ?>"><?php echo $challenge['progress']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 0.5rem;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $challenge['progress']; ?>%" aria-valuenow="<?php echo $challenge['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if (count($userChallenges) > 3): ?>
                            <li class="list-group-item text-center">
                                <a href="#challenges" class="text-decoration-none">View all challenges</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Profile</h5>
            </div>
            <div class="card-body">
                <form action="profile.php" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="age" name="age" min="0" max="120" value="<?php echo (int)($user['age'] ?? 0); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="" <?php echo ($user['gender'] ?? '') === '' ? 'selected' : ''; ?>>Prefer not to say</option>
                                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" class="form-control" id="height" name="height" min="0" step="0.1" value="<?php echo (float)($user['height'] ?? 0); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" min="0" step="0.1" value="<?php echo (float)($user['weight'] ?? 0); ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <input type="hidden" name="update_profile" value="1">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="profile.php" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid">
                        <input type="hidden" name="change_password" value="1">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div id="challenges" class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Challenges</h5>
            </div>
            <div class="card-body">
                <?php if (empty($userChallenges)): ?>
                    <div class="text-center p-4">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <h5>No Challenges Yet</h5>
                        <p>You haven't joined any challenges yet. Challenges are a great way to stay motivated!</p>
                        <a href="challenges.php" class="btn btn-primary">Explore Challenges</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Challenge</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userChallenges as $challenge): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($challenge['name']); ?></td>
                                        <td><?php echo $challenge['duration']; ?> days</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $challenge['status'] === 'completed' ? 'success' : 
                                                    ($challenge['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php 
                                                echo ucfirst(str_replace('_', ' ', $challenge['status']));
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 0.5rem; width: 100px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $challenge['progress']; ?>%" aria-valuenow="<?php echo $challenge['progress']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small><?php echo $challenge['progress']; ?>%</small>
                                        </td>
                                        <td>
                                            <a href="challenge-detail.php?id=<?php echo $challenge['challenge_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
