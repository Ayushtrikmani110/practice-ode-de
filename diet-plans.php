<?php
$pageTitle = 'Diet Plans';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get all diet plans
$db = Database::getInstance();
$conn = $db->getConnection();

// Get filter parameters
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$caloriesMin = isset($_GET['calories_min']) ? (int)$_GET['calories_min'] : 0;
$caloriesMax = isset($_GET['calories_max']) ? (int)$_GET['calories_max'] : 3000;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build the SQL query
$sql = "SELECT * FROM diet_plans WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($caloriesMin > 0) {
    $sql .= " AND calories >= ?";
    $params[] = $caloriesMin;
    $types .= "i";
}

if ($caloriesMax < 3000) {
    $sql .= " AND calories <= ?";
    $params[] = $caloriesMax;
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
$dietPlans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get distinct categories for filters
$categories = $conn->query("SELECT DISTINCT category FROM diet_plans ORDER BY category")->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <h1 class="fw-bold">Diet Plans</h1>
    <p class="lead">Discover nutrition plans tailored to your fitness goals</p>
</div>

<div class="row">
    <!-- Sidebar Filters -->
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form action="diet-plans.php" method="get" id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="dietSearch" placeholder="Search diet plans..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <label class="form-label">Calories Range</label>
                        <div class="d-flex align-items-center">
                            <input type="number" class="form-control me-2" name="calories_min" placeholder="Min" value="<?php echo $caloriesMin ?: ''; ?>">
                            <span>to</span>
                            <input type="number" class="form-control ms-2" name="calories_max" placeholder="Max" value="<?php echo $caloriesMax !== 3000 ? $caloriesMax : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="diet-plans.php" class="btn btn-outline-secondary mt-2">Reset Filters</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Nutrition Tips -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Nutrition Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Stay hydrated by drinking at least 8 glasses of water daily.</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Include protein with every meal to support muscle recovery.</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Eat a variety of colorful fruits and vegetables for essential nutrients.</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Choose whole grains over refined carbohydrates for sustained energy.</span>
                    </li>
                    <li class="list-group-item d-flex">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Plan and prepare meals in advance to avoid unhealthy food choices.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Diet Plan Cards -->
    <div class="col-lg-9">
        <?php if (empty($dietPlans)): ?>
            <div class="alert alert-info">
                <h4 class="alert-heading">No diet plans found!</h4>
                <p>Try adjusting your search criteria or filters to find diet plans.</p>
            </div>
        <?php else: ?>
            <!-- Diet plan count -->
            <p class="text-muted mb-4">Showing <?php echo count($dietPlans); ?> diet plan(s)</p>
            
            <div class="row g-4">
                <?php foreach ($dietPlans as $dietPlan): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card diet-plan-card h-100">
                            <img src="<?php echo htmlspecialchars($dietPlan['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dietPlan['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($dietPlan['name']); ?></h5>
                                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($dietPlan['category']); ?></span>
                                <p class="card-text"><?php echo substr(htmlspecialchars($dietPlan['description']), 0, 100) . '...'; ?></p>
                                <div class="nutrition-info">
                                    <div class="nutrition-item">
                                        <span>Calories:</span>
                                        <span><?php echo $dietPlan['calories']; ?> kcal</span>
                                    </div>
                                    <div class="nutrition-item">
                                        <span>Protein:</span>
                                        <span><?php echo $dietPlan['protein']; ?>g</span>
                                    </div>
                                    <div class="nutrition-item">
                                        <span>Carbs:</span>
                                        <span><?php echo $dietPlan['carbs']; ?>g</span>
                                    </div>
                                    <div class="nutrition-item">
                                        <span>Fat:</span>
                                        <span><?php echo $dietPlan['fat']; ?>g</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="diet-plan-detail.php?id=<?php echo $dietPlan['id']; ?>" class="btn btn-outline-primary w-100">View Plan</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
