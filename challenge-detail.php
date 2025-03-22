<?php
$pageTitle = 'Challenge Details';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid challenge ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: challenges.php');
    exit;
}

$challengeId = (int)$_GET['id'];

// Get challenge details
$challenge = getChallengeById($challengeId);

if (!$challenge) {
    $_SESSION['message'] = 'Challenge not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: challenges.php');
    exit;
}

// Get user's challenge data if logged in
$userChallenge = null;
$isParticipating = false;

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM user_challenges WHERE user_id = ? AND challenge_id = ?");
    $stmt->bind_param("ii", $userId, $challengeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userChallenge = $result->fetch_assoc();
        $isParticipating = true;
    }
}

// Calculate milestones based on challenge duration
$milestones = [];
$daysPerMilestone = max(1, ceil($challenge['duration'] / 5)); // Create about 5 milestones

for ($i = 0; $i <= $challenge['duration']; $i += $daysPerMilestone) {
    if ($i > 0) { // Skip day 0
        $progress = min(100, round(($i / $challenge['duration']) * 100));
        $milestones[] = [
            'day' => $i,
            'progress' => $progress,
            'description' => "Day $i: " . ($i === $challenge['duration'] ? "Challenge complete!" : "Complete " . $progress . "% of the challenge")
        ];
    }
}

// Add extra scripts
$extraScripts = ['assets/js/challenge.js'];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <div class="container">
        <h1 class="fw-bold"><?php echo htmlspecialchars($challenge['name']); ?></h1>
        <div class="d-flex justify-content-center gap-3 mt-2">
            <span class="badge bg-<?php 
                echo $challenge['difficulty'] === 'Beginner' ? 'success' : 
                    ($challenge['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
            ?> fs-6"><?php echo htmlspecialchars($challenge['difficulty']); ?></span>
            <span class="badge bg-info fs-6"><?php echo $challenge['duration']; ?> Days</span>
            <span class="badge bg-secondary fs-6"><i class="fas fa-trophy me-1"></i> <?php echo $challenge['points']; ?> Points</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 shadow-sm">
            <img src="<?php echo htmlspecialchars($challenge['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($challenge['name']); ?>" style="max-height: 400px; object-fit: cover;">
            <div class="card-body p-4">
                <h2 class="mb-3">Challenge Description</h2>
                <p class="lead"><?php echo htmlspecialchars($challenge['description']); ?></p>
                
                <h4 class="mt-4 mb-3">What You'll Achieve</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon bg-primary text-white me-3">
                                <i class="fas fa-fire"></i>
                            </div>
                            <span>Improved Endurance</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon bg-primary text-white me-3">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <span>Increased Strength</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon bg-primary text-white me-3">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <span>Better Discipline</span>
                        </div>
                    </div>
                </div>
                
                <h4 class="mt-4 mb-3">Challenge Milestones</h4>
                <div class="milestones-container">
                    <?php foreach ($milestones as $index => $milestone): ?>
                        <div class="milestone mb-3 <?php 
                            if ($isParticipating) {
                                echo $userChallenge['progress'] >= $milestone['progress'] ? 'completed' : 
                                    ($index === 0 || ($index > 0 && $userChallenge['progress'] >= $milestones[$index-1]['progress']) ? 'current' : 'upcoming');
                            } else {
                                echo $index === 0 ? 'current' : 'upcoming';
                            }
                        ?>">
                            <h5 class="mb-1">Milestone <?php echo $index + 1; ?></h5>
                            <p class="mb-0"><?php echo $milestone['description']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Challenge Tips</h4>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Be consistent:</strong> Try to make daily progress, even if it's small.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Track your progress:</strong> Update your progress regularly to stay motivated.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Rest when needed:</strong> Allow for recovery days to prevent burnout or injury.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Adjust as necessary:</strong> Modify the challenge to fit your fitness level if needed.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Stay hydrated:</strong> Drink plenty of water throughout the challenge.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <?php if ($isParticipating): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Your Progress</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <span class="badge bg-<?php 
                            echo $userChallenge['status'] === 'completed' ? 'success' : 
                                ($userChallenge['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                        ?> fs-6 challenge-status">
                            <?php echo ucfirst(str_replace('_', ' ', $userChallenge['status'])); ?>
                        </span>
                    </div>
                    
                    <h5 class="text-center mb-3">
                        <?php echo $userChallenge['progress']; ?>% Complete
                    </h5>
                    
                    <div class="progress challenge-progress-bar mb-4">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $userChallenge['progress']; ?>%" aria-valuenow="<?php echo $userChallenge['progress']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $userChallenge['progress']; ?>%</div>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Started: <?php echo formatDate($userChallenge['start_date']); ?></span>
                        <?php if ($userChallenge['completion_date']): ?>
                            <span>Completed: <?php echo formatDate($userChallenge['completion_date']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($userChallenge['status'] !== 'completed'): ?>
                        <form id="updateProgressForm" action="update-progress.php" method="post" data-challenge-id="<?php echo $userChallenge['id']; ?>">
                            <div class="mb-3">
                                <label for="progressRange" class="form-label">Update Your Progress</label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="range" class="form-range flex-grow-1" min="0" max="100" step="5" id="progressRange" name="progress" value="<?php echo $userChallenge['progress']; ?>">
                                    <span id="progressValue" class="badge bg-primary"><?php echo $userChallenge['progress']; ?>%</span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="user_challenge_id" value="<?php echo $userChallenge['id']; ?>">
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update Progress</button>
                            </div>
                        </form>
                        
                        <div id="completionMessage" class="alert alert-success mt-3 <?php echo $userChallenge['progress'] >= 100 ? '' : 'd-none'; ?>">
                            <h5 class="alert-heading">Challenge Completed! 🎉</h5>
                            <p>Congratulations on completing this challenge! You've earned <?php echo $challenge['points']; ?> points.</p>
                            <hr>
                            <p class="mb-0">Keep the momentum going by joining another challenge!</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <h5 class="alert-heading">Challenge Completed! 🎉</h5>
                            <p>Congratulations on completing this challenge! You've earned <?php echo $challenge['points']; ?> points.</p>
                            <hr>
                            <p class="mb-0">Keep the momentum going by joining another challenge!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Join This Challenge</h4>
                </div>
                <div class="card-body p-4">
                    <p>Ready to push yourself? Join this <?php echo $challenge['duration']; ?>-day challenge and track your progress!</p>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Duration: <?php echo $challenge['duration']; ?> days
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-signal text-primary me-2"></i>
                            Difficulty: <?php echo htmlspecialchars($challenge['difficulty']); ?>
                        </li>
                        <li class="list-group-item px-0">
                            <i class="fas fa-trophy text-primary me-2"></i>
                            Reward: <?php echo $challenge['points']; ?> points
                        </li>
                    </ul>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg join-challenge-btn" data-challenge-id="<?php echo $challenge['id']; ?>">
                                Join Challenge
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="d-grid">
                            <a href="login.php" class="btn btn-primary btn-lg">Sign In to Join</a>
                        </div>
                        <p class="text-center mt-2">
                            <small>Don't have an account? <a href="register.php">Register now</a></small>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Similar Challenges</h4>
            </div>
            <div class="card-body p-0">
                <?php
                // Get similar challenges
                $sql = "SELECT * FROM challenges 
                        WHERE difficulty = ? 
                        AND id != ? 
                        ORDER BY RAND() 
                        LIMIT 3";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $challenge['difficulty'], $challengeId);
                $stmt->execute();
                $similarChallenges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                if (!empty($similarChallenges)):
                ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($similarChallenges as $similarChallenge): ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($similarChallenge['image_url']); ?>" class="rounded me-3" width="60" height="60" style="object-fit: cover;" alt="<?php echo htmlspecialchars($similarChallenge['name']); ?>">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($similarChallenge['name']); ?></h6>
                                        <small class="text-muted"><?php echo $similarChallenge['duration']; ?> days</small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="challenge-detail.php?id=<?php echo $similarChallenge['id']; ?>" class="btn btn-sm btn-outline-primary">View Challenge</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <p class="mb-0">No similar challenges found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
