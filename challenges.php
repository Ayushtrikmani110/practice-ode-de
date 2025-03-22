<?php
$pageTitle = 'Challenges';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get all challenges
$db = Database::getInstance();
$conn = $db->getConnection();

// Get filter parameters
$difficulty = isset($_GET['difficulty']) ? sanitizeInput($_GET['difficulty']) : '';
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 0;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build the SQL query
$sql = "SELECT * FROM challenges WHERE 1=1";
$params = [];
$types = "";

if (!empty($difficulty)) {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

if ($duration > 0) {
    $sql .= " AND duration <= ?";
    $params[] = $duration;
    $types .= "i";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$sql .= " ORDER BY name ASC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$challenges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct difficulties for filters
$difficulties = $conn->query("SELECT DISTINCT difficulty FROM challenges ORDER BY FIELD(difficulty, 'Beginner', 'Intermediate', 'Advanced')")->fetch_all(MYSQLI_ASSOC);

// Get user's active challenges if logged in
$userChallenges = [];
if (isLoggedIn()) {
    $userChallenges = getUserChallenges($_SESSION['user_id']);
    
    // Create an array of challenge IDs that the user is already participating in
    $userChallengeIds = array_column($userChallenges, 'challenge_id');
}

// Add extra scripts
$extraScripts = ['assets/js/challenge.js'];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <h1 class="fw-bold">Fitness Challenges</h1>
    <p class="lead">Push your limits and achieve your goals with our fitness challenges</p>
</div>

<!-- Alert container for dynamic messages -->
<div id="alertContainer"></div>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="challenges.php" method="get" id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="challengeSearch" placeholder="Search challenges..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Difficulty</label>
                        <select class="form-select" name="difficulty" id="difficultyFilter">
                            <option value="">All Difficulties</option>
                            <?php foreach ($difficulties as $diff): ?>
                                <option value="<?php echo htmlspecialchars($diff['difficulty']); ?>" <?php echo $difficulty === $diff['difficulty'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($diff['difficulty']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Max Duration (days)</label>
                        <select class="form-select" name="duration" id="durationFilter">
                            <option value="0" <?php echo $duration === 0 ? 'selected' : ''; ?>>Any Duration</option>
                            <option value="7" <?php echo $duration === 7 ? 'selected' : ''; ?>>Up to 1 week</option>
                            <option value="14" <?php echo $duration === 14 ? 'selected' : ''; ?>>Up to 2 weeks</option>
                            <option value="30" <?php echo $duration === 30 ? 'selected' : ''; ?>>Up to 1 month</option>
                            <option value="60" <?php echo $duration === 60 ? 'selected' : ''; ?>>Up to 2 months</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="challenges.php" class="btn btn-outline-secondary mt-2">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Challenge Benefits -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Benefits of Challenges</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex">
                        <i class="fas fa-trophy text-warning me-2 mt-1"></i>
                        <span>Stay motivated with clear goals and deadlines</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-trophy text-warning me-2 mt-1"></i>
                        <span>Track your progress and celebrate milestones</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-trophy text-warning me-2 mt-1"></i>
                        <span>Build consistency in your fitness routine</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-trophy text-warning me-2 mt-1"></i>
                        <span>Challenge yourself to try new exercises</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-trophy text-warning me-2 mt-1"></i>
                        <span>Earn points and rewards for your achievements</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Challenge Cards -->
    <div class="col-lg-9">
        <?php if (empty($challenges)): ?>
            <div class="alert alert-info">
                <h4 class="alert-heading">No challenges found!</h4>
                <p>Try adjusting your search criteria or filters to find challenges.</p>
            </div>
        <?php else: ?>
            <!-- Filter buttons -->
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary challenge-filter-btn active" data-difficulty="all">All</button>
                    <?php 
                    $uniqueDifficulties = [];
                    foreach ($difficulties as $diff) {
                        if (!in_array($diff['difficulty'], $uniqueDifficulties)) {
                            $uniqueDifficulties[] = $diff['difficulty'];
                    ?>
                        <button class="btn btn-outline-primary challenge-filter-btn" data-difficulty="<?php echo htmlspecialchars($diff['difficulty']); ?>">
                            <?php echo htmlspecialchars($diff['difficulty']); ?>
                        </button>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Challenge count -->
            <p class="text-muted mb-4">Showing <?php echo count($challenges); ?> challenge(s)</p>
            
            <div class="row g-4">
                <?php foreach ($challenges as $challenge): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card challenge-card h-100" data-difficulty="<?php echo htmlspecialchars($challenge['difficulty']); ?>" data-duration="<?php echo $challenge['duration']; ?>">
                            <img src="<?php echo htmlspecialchars($challenge['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($challenge['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($challenge['name']); ?></h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-<?php 
                                        echo $challenge['difficulty'] === 'Beginner' ? 'success' : 
                                            ($challenge['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
                                    ?>"><?php echo htmlspecialchars($challenge['difficulty']); ?></span>
                                    <span class="badge bg-info"><?php echo $challenge['duration']; ?> Days</span>
                                </div>
                                <p class="card-text"><?php echo substr(htmlspecialchars($challenge['description']), 0, 100) . '...'; ?></p>
                                <div class="d-flex align-items-center mt-3">
                                    <i class="fas fa-trophy text-warning me-2"></i>
                                    <span><?php echo $challenge['points']; ?> Points</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                                <a href="challenge-detail.php?id=<?php echo $challenge['id']; ?>" class="btn btn-outline-primary flex-grow-1 me-2">View Details</a>
                                
                                <?php if (isLoggedIn()): ?>
                                    <?php if (in_array($challenge['id'], $userChallengeIds)): ?>
                                        <button class="btn btn-success" disabled>
                                            <i class="fas fa-check"></i> Joined
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary join-challenge-btn" data-challenge-id="<?php echo $challenge['id']; ?>">
                                            Join
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary">Sign In to Join</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
