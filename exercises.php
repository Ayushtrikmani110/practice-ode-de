<?php
$pageTitle = 'Exercises';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get all exercises
$db = Database::getInstance();
$conn = $db->getConnection();

// Get filter parameters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$difficulty = isset($_GET['difficulty']) ? sanitizeInput($_GET['difficulty']) : '';
$targetMuscle = isset($_GET['muscle']) ? sanitizeInput($_GET['muscle']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build the SQL query
$sql = "SELECT * FROM exercises WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($difficulty)) {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

if (!empty($targetMuscle)) {
    $sql .= " AND target_muscle = ?";
    $params[] = $targetMuscle;
    $types .= "s";
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
$exercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct categories, difficulties, and target muscles for filters
$categories = $conn->query("SELECT DISTINCT category FROM exercises ORDER BY category")->fetch_all(MYSQLI_ASSOC);
$difficulties = $conn->query("SELECT DISTINCT difficulty FROM exercises ORDER BY FIELD(difficulty, 'Beginner', 'Intermediate', 'Advanced')")->fetch_all(MYSQLI_ASSOC);
$targetMuscles = $conn->query("SELECT DISTINCT target_muscle FROM exercises ORDER BY target_muscle")->fetch_all(MYSQLI_ASSOC);

// Add extra scripts
$extraScripts = ['assets/js/exercise.js'];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <h1 class="fw-bold">Exercise Library</h1>
    <p class="lead">Find the perfect exercises for your fitness goals</p>
</div>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="exercises.php" method="get" id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="exerciseSearch" placeholder="Search exercises..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        <label class="form-label">Target Muscle</label>
                        <select class="form-select" name="muscle" id="muscleFilter">
                            <option value="">All Muscles</option>
                            <?php foreach ($targetMuscles as $muscle): ?>
                                <option value="<?php echo htmlspecialchars($muscle['target_muscle']); ?>" <?php echo $targetMuscle === $muscle['target_muscle'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($muscle['target_muscle']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="exercises.php" class="btn btn-outline-secondary mt-2">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Navigation -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="exercises.php?category=Strength" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Strength Training
                        <i class="fas fa-dumbbell"></i>
                    </a>
                    <a href="exercises.php?category=Cardio" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Cardio Workouts
                        <i class="fas fa-heartbeat"></i>
                    </a>
                    <a href="exercises.php?category=Core" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Core Exercises
                        <i class="fas fa-fire"></i>
                    </a>
                    <a href="exercises.php?difficulty=Beginner" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Beginner Friendly
                        <i class="fas fa-star"></i>
                    </a>
                    <a href="exercises.php?difficulty=Advanced" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Advanced Exercises
                        <i class="fas fa-trophy"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Exercise Cards -->
    <div class="col-lg-9">
        <?php if (empty($exercises)): ?>
            <div class="alert alert-info">
                <h4 class="alert-heading">No exercises found!</h4>
                <p>Try adjusting your search criteria or filters to find exercises.</p>
            </div>
        <?php else: ?>
            <!-- Filter buttons -->
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary exercise-filter-btn active" data-category="all">All</button>
                    <?php 
                    $uniqueCategories = [];
                    foreach ($exercises as $exercise) {
                        if (!in_array($exercise['category'], $uniqueCategories)) {
                            $uniqueCategories[] = $exercise['category'];
                    ?>
                        <button class="btn btn-outline-primary exercise-filter-btn" data-category="<?php echo htmlspecialchars($exercise['category']); ?>">
                            <?php echo htmlspecialchars($exercise['category']); ?>
                        </button>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Exercise count -->
            <p class="text-muted mb-4">Showing <?php echo count($exercises); ?> exercise(s)</p>
            
            <div class="row g-4">
                <?php foreach ($exercises as $exercise): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card exercise-card h-100" data-category="<?php echo htmlspecialchars($exercise['category']); ?>" data-difficulty="<?php echo htmlspecialchars($exercise['difficulty']); ?>">
                            <div class="card-img-wrapper">
                                <img src="<?php echo htmlspecialchars($exercise['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($exercise['name']); ?>">
                                <span class="badge bg-<?php 
                                    echo $exercise['difficulty'] === 'Beginner' ? 'success' : 
                                        ($exercise['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
                                ?> difficulty-badge"><?php echo htmlspecialchars($exercise['difficulty']); ?></span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($exercise['name']); ?></h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($exercise['description']), 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($exercise['target_muscle']); ?></span>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($exercise['category']); ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="exercise-detail.php?id=<?php echo $exercise['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
