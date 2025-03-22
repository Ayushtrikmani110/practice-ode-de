<?php
$pageTitle = 'Diet Plan Details';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid diet plan ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: diet-plans.php');
    exit;
}

$dietPlanId = (int)$_GET['id'];

// Get diet plan details
$dietPlan = getDietPlanById($dietPlanId);

if (!$dietPlan) {
    $_SESSION['message'] = 'Diet plan not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: diet-plans.php');
    exit;
}

// Get similar diet plans
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT * FROM diet_plans 
        WHERE category = ? 
        AND id != ? 
        ORDER BY RAND() 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $dietPlan['category'], $dietPlanId);
$stmt->execute();
$similarDietPlans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <div class="container">
        <h1 class="fw-bold"><?php echo htmlspecialchars($dietPlan['name']); ?></h1>
        <div class="d-flex justify-content-center gap-3 mt-2">
            <span class="badge bg-info fs-6"><?php echo htmlspecialchars($dietPlan['category']); ?></span>
            <span class="badge bg-secondary fs-6"><?php echo $dietPlan['calories']; ?> Calories</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 shadow-sm">
            <img src="<?php echo htmlspecialchars($dietPlan['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($dietPlan['name']); ?>" style="max-height: 400px; object-fit: cover;">
            <div class="card-body p-4">
                <h2 class="mb-3">Plan Overview</h2>
                <p class="lead"><?php echo htmlspecialchars($dietPlan['description']); ?></p>
                
                <div class="nutrition-info bg-light p-3 rounded mt-4">
                    <h4 class="mb-3">Nutritional Information</h4>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <h5 class="fw-bold text-primary mb-0"><?php echo $dietPlan['calories']; ?></h5>
                                <p class="text-muted mb-0">Calories</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <h5 class="fw-bold text-primary mb-0"><?php echo $dietPlan['protein']; ?>g</h5>
                                <p class="text-muted mb-0">Protein</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <h5 class="fw-bold text-primary mb-0"><?php echo $dietPlan['carbs']; ?>g</h5>
                                <p class="text-muted mb-0">Carbohydrates</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <h5 class="fw-bold text-primary mb-0"><?php echo $dietPlan['fat']; ?>g</h5>
                                <p class="text-muted mb-0">Fat</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Macronutrient Breakdown -->
                    <div class="mt-3">
                        <h6 class="mb-2">Macronutrient Breakdown</h6>
                        <div class="progress" style="height: 1.5rem;">
                            <?php 
                            $totalCals = ($dietPlan['protein'] * 4) + ($dietPlan['carbs'] * 4) + ($dietPlan['fat'] * 9);
                            $proteinPct = round(($dietPlan['protein'] * 4) / $totalCals * 100);
                            $carbsPct = round(($dietPlan['carbs'] * 4) / $totalCals * 100);
                            $fatPct = round(($dietPlan['fat'] * 9) / $totalCals * 100);
                            ?>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $proteinPct; ?>%" aria-valuenow="<?php echo $proteinPct; ?>" aria-valuemin="0" aria-valuemax="100">Protein <?php echo $proteinPct; ?>%</div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $carbsPct; ?>%" aria-valuenow="<?php echo $carbsPct; ?>" aria-valuemin="0" aria-valuemax="100">Carbs <?php echo $carbsPct; ?>%</div>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $fatPct; ?>%" aria-valuenow="<?php echo $fatPct; ?>" aria-valuemin="0" aria-valuemax="100">Fat <?php echo $fatPct; ?>%</div>
                        </div>
                    </div>
                </div>
                
                <h3 class="mt-5 mb-4">Sample Meal Plan</h3>
                
                <!-- Breakfast -->
                <div class="meal">
                    <h4><i class="fas fa-sun me-2"></i> Breakfast</h4>
                    <?php 
                    // Sample breakfast items based on diet type
                    $breakfastItems = [];
                    
                    switch($dietPlan['category']) {
                        case 'Keto':
                            $breakfastItems = [
                                "Scrambled eggs with avocado and bacon",
                                "Coffee with heavy cream or bulletproof coffee",
                                "Sugar-free Greek yogurt with nuts and seeds"
                            ];
                            break;
                        case 'Vegan':
                        case 'Vegetarian':
                            $breakfastItems = [
                                "Overnight oats with plant-based milk, chia seeds, and berries",
                                "Tofu scramble with vegetables and whole grain toast",
                                "Green smoothie with spinach, banana, and plant protein"
                            ];
                            break;
                        case 'Paleo':
                            $breakfastItems = [
                                "Sweet potato and turkey hash with vegetables",
                                "Egg muffins with vegetables and herbs",
                                "Fresh fruit with almond butter"
                            ];
                            break;
                        case 'Mediterranean':
                            $breakfastItems = [
                                "Greek yogurt with honey, nuts, and fresh fruit",
                                "Whole grain toast with avocado and olive oil",
                                "Vegetable omelette with feta cheese"
                            ];
                            break;
                        default:
                            $breakfastItems = [
                                "Protein smoothie with whey protein, banana, and berries",
                                "Oatmeal with nuts, seeds, and fresh fruit",
                                "Whole grain toast with eggs and avocado"
                            ];
                    }
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($breakfastItems as $item): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-utensils me-2 text-primary"></i> <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Lunch -->
                <div class="meal">
                    <h4><i class="fas fa-cloud-sun me-2"></i> Lunch</h4>
                    <?php 
                    // Sample lunch items based on diet type
                    $lunchItems = [];
                    
                    switch($dietPlan['category']) {
                        case 'Keto':
                            $lunchItems = [
                                "Salad with grilled chicken, avocado, and high-fat dressing",
                                "Bunless burger with cheese, lettuce wrap, and side salad",
                                "Zucchini noodles with alfredo sauce and grilled salmon"
                            ];
                            break;
                        case 'Vegan':
                        case 'Vegetarian':
                            $lunchItems = [
                                "Quinoa bowl with roasted vegetables and tahini dressing",
                                "Lentil soup with side salad and whole grain bread",
                                "Plant-based protein wrap with hummus and vegetables"
                            ];
                            break;
                        case 'Paleo':
                            $lunchItems = [
                                "Grilled chicken over mixed greens with olive oil and lemon",
                                "Stuffed bell peppers with ground turkey and vegetables",
                                "Tuna salad in lettuce wraps with avocado"
                            ];
                            break;
                        case 'Mediterranean':
                            $lunchItems = [
                                "Greek salad with olives, feta, and olive oil dressing",
                                "Whole grain pita with hummus, vegetables, and grilled chicken",
                                "Lentil soup with olive oil drizzle and fresh herbs"
                            ];
                            break;
                        default:
                            $lunchItems = [
                                "Grilled chicken salad with mixed vegetables and vinaigrette",
                                "Whole grain wrap with lean protein and plenty of vegetables",
                                "Quinoa bowl with roasted vegetables and lean protein"
                            ];
                    }
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($lunchItems as $item): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-utensils me-2 text-primary"></i> <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Dinner -->
                <div class="meal">
                    <h4><i class="fas fa-moon me-2"></i> Dinner</h4>
                    <?php 
                    // Sample dinner items based on diet type
                    $dinnerItems = [];
                    
                    switch($dietPlan['category']) {
                        case 'Keto':
                            $dinnerItems = [
                                "Baked salmon with asparagus and butter sauce",
                                "Ribeye steak with cauliflower mash and roasted Brussels sprouts",
                                "Chicken thighs with creamy mushroom sauce and zucchini noodles"
                            ];
                            break;
                        case 'Vegan':
                        case 'Vegetarian':
                            $dinnerItems = [
                                "Chickpea curry with brown rice and steamed vegetables",
                                "Stuffed bell peppers with quinoa, black beans, and vegetables",
                                "Vegetable stir-fry with tofu and brown rice"
                            ];
                            break;
                        case 'Paleo':
                            $dinnerItems = [
                                "Grilled steak with roasted sweet potatoes and asparagus",
                                "Baked salmon with roasted vegetables and fresh herbs",
                                "Roasted chicken with root vegetables and olive oil"
                            ];
                            break;
                        case 'Mediterranean':
                            $dinnerItems = [
                                "Grilled fish with lemon, olive oil, and fresh herbs",
                                "Whole grain pasta with tomato sauce, vegetables, and olive oil",
                                "Chicken souvlaki with Greek salad and tzatziki"
                            ];
                            break;
                        default:
                            $dinnerItems = [
                                "Lean protein (chicken/fish/turkey) with roasted vegetables",
                                "Whole grain pasta with lean protein and tomato sauce",
                                "Stir-fry with lean protein, vegetables, and brown rice"
                            ];
                    }
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($dinnerItems as $item): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-utensils me-2 text-primary"></i> <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Snacks -->
                <div class="meal">
                    <h4><i class="fas fa-apple-alt me-2"></i> Snacks</h4>
                    <?php 
                    // Sample snack items based on diet type
                    $snackItems = [];
                    
                    switch($dietPlan['category']) {
                        case 'Keto':
                            $snackItems = [
                                "Cheese slices with olives",
                                "Hard-boiled eggs with salt and pepper",
                                "Celery sticks with full-fat cream cheese",
                                "Handful of mixed nuts"
                            ];
                            break;
                        case 'Vegan':
                        case 'Vegetarian':
                            $snackItems = [
                                "Apple slices with almond butter",
                                "Hummus with carrot and cucumber sticks",
                                "Trail mix with nuts, seeds, and dried fruit",
                                "Plant-based protein smoothie"
                            ];
                            break;
                        case 'Paleo':
                            $snackItems = [
                                "Fresh fruit with almond butter",
                                "Beef or turkey jerky (sugar-free)",
                                "Mixed nuts and seeds",
                                "Hard-boiled eggs"
                            ];
                            break;
                        case 'Mediterranean':
                            $snackItems = [
                                "Greek yogurt with honey and walnuts",
                                "Fresh fruit with a small handful of nuts",
                                "Hummus with vegetable sticks",
                                "Olives and a small piece of cheese"
                            ];
                            break;
                        default:
                            $snackItems = [
                                "Greek yogurt with berries",
                                "Protein bar (check sugar content)",
                                "Apple with nut butter",
                                "Small handful of nuts and dried fruit"
                            ];
                    }
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($snackItems as $item): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-utensils me-2 text-primary"></i> <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Plan Benefits</h4>
            </div>
            <div class="card-body p-4">
                <?php
                // Benefits based on diet type
                $benefits = [];
                
                switch($dietPlan['category']) {
                    case 'Keto':
                        $benefits = [
                            "Promotes fat loss while preserving muscle mass",
                            "May improve mental clarity and focus",
                            "Can help reduce hunger and cravings",
                            "May help manage blood sugar levels",
                            "Potential benefits for certain neurological conditions"
                        ];
                        break;
                    case 'Vegan':
                    case 'Vegetarian':
                        $benefits = [
                            "Rich in fiber, vitamins, and antioxidants",
                            "Lower risk of heart disease and certain cancers",
                            "May help maintain healthy weight",
                            "Lower carbon footprint and environmental impact",
                            "May improve digestive health"
                        ];
                        break;
                    case 'Paleo':
                        $benefits = [
                            "Focus on whole, unprocessed foods",
                            "Rich in lean proteins and vegetables",
                            "Eliminates processed sugars and grains",
                            "May help reduce inflammation",
                            "Supports stable blood sugar levels"
                        ];
                        break;
                    case 'Mediterranean':
                        $benefits = [
                            "Heart-healthy fats from olive oil and nuts",
                            "Rich in antioxidants and anti-inflammatory compounds",
                            "Associated with longevity and healthy aging",
                            "May help protect against chronic diseases",
                            "Emphasizes enjoyable, social eating habits"
                        ];
                        break;
                    case 'Intermittent Fasting':
                        $benefits = [
                            "May help with weight management",
                            "Potential benefits for cellular repair processes",
                            "May improve insulin sensitivity",
                            "Flexible approach to meal timing",
                            "Possible benefits for brain health"
                        ];
                        break;
                    case 'Clean Eating':
                        $benefits = [
                            "Focus on whole, minimally processed foods",
                            "Elimination of artificial ingredients",
                            "Rich in nutrients from varied food sources",
                            "May help improve energy levels",
                            "Supports overall health and wellbeing"
                        ];
                        break;
                    case 'Weight Loss':
                    case 'Muscle Gain':
                        $benefits = [
                            "Balanced macronutrient profile",
                            "Optimized for your fitness goals",
                            "Supports exercise performance and recovery",
                            "Helps maintain energy levels throughout the day",
                            "Sustainable approach to nutrition"
                        ];
                        break;
                    default:
                        $benefits = [
                            "Balanced nutrition to support overall health",
                            "Variety of food choices for essential nutrients",
                            "Supports your active lifestyle",
                            "Emphasizes whole foods over processed options",
                            "Flexible approach to healthy eating"
                        ];
                }
                ?>
                
                <ul class="list-group list-group-flush">
                    <?php foreach($benefits as $benefit): ?>
                        <li class="list-group-item px-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <?php echo $benefit; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Similar Diet Plans</h4>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($similarDietPlans)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($similarDietPlans as $similarPlan): ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($similarPlan['image_url']); ?>" class="rounded me-3" width="60" height="60" style="object-fit: cover;" alt="<?php echo htmlspecialchars($similarPlan['name']); ?>">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($similarPlan['name']); ?></h6>
                                        <small class="text-muted"><?php echo $similarPlan['calories']; ?> calories</small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="diet-plan-detail.php?id=<?php echo $similarPlan['id']; ?>" class="btn btn-sm btn-outline-primary">View Plan</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <p class="mb-0">No similar diet plans found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Nutrition Tips</h4>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Meal prep:</strong> Prepare meals in advance to ensure you stick to the plan.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Stay hydrated:</strong> Drink at least 8 glasses of water daily.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Portion control:</strong> Use measuring cups or a food scale until you can eyeball portions accurately.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Eat slowly:</strong> Take time to enjoy your food and recognize when you're full.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>80/20 rule:</strong> Aim to follow the plan 80% of the time, allowing 20% flexibility for occasional treats.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
