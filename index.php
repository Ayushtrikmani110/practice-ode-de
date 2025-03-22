<?php
$pageTitle = 'Home';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get featured exercises
$db = Database::getInstance();
$conn = $db->getConnection();

$featuredExercises = $conn->query("SELECT * FROM exercises ORDER BY RAND() LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$featuredDietPlans = $conn->query("SELECT * FROM diet_plans ORDER BY RAND() LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$featuredChallenges = $conn->query("SELECT * FROM challenges ORDER BY RAND() LIMIT 3")->fetch_all(MYSQLI_ASSOC);
?>

<!-- Hero Section -->
<section class="hero-section" style="background-image: url('https://images.unsplash.com/photo-1518644961665-ed172691aaa1');">
    <div class="container hero-content text-center">
        <h1 class="display-4 mb-4">Transform Your Body, Transform Your Life</h1>
        <p class="lead mb-4">Join FitLife Pro for personalized workouts, nutrition plans, and fitness challenges tailored to your goals.</p>
        <div class="d-flex justify-content-center gap-3">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg">Join Now</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Sign In</a>
            <?php else: ?>
                <a href="exercises.php" class="btn btn-primary btn-lg">Start Workout</a>
                <a href="challenges.php" class="btn btn-outline-light btn-lg">Join a Challenge</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose FitLife Pro?</h2>
            <p class="lead text-muted">Everything you need for your fitness journey in one place</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature text-center p-4">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Expert Workouts</h3>
                    <p>Access hundreds of professionally designed workouts for all fitness levels and age groups.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature text-center p-4">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Customized Nutrition</h3>
                    <p>Get personalized diet plans based on your goals, preferences, and dietary restrictions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature text-center p-4">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Fun Challenges</h3>
                    <p>Stay motivated with our community challenges and track your progress toward your goals.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Exercises -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Exercises</h2>
            <a href="exercises.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredExercises as $exercise): ?>
                <div class="col-md-4">
                    <div class="card exercise-card h-100">
                        <div class="card-img-wrapper">
                            <img src="<?php echo $exercise['image_url']; ?>" class="card-img-top" alt="<?php echo $exercise['name']; ?>">
                            <span class="badge bg-<?php 
                                echo $exercise['difficulty'] === 'Beginner' ? 'success' : 
                                    ($exercise['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
                            ?> difficulty-badge"><?php echo $exercise['difficulty']; ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $exercise['name']; ?></h5>
                            <p class="card-text"><?php echo substr($exercise['description'], 0, 100) . '...'; ?></p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-secondary"><?php echo $exercise['target_muscle']; ?></span>
                                <span class="badge bg-info"><?php echo $exercise['category']; ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="exercise-detail.php?id=<?php echo $exercise['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Diet Plans -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Diet Plans</h2>
            <a href="diet-plans.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredDietPlans as $dietPlan): ?>
                <div class="col-md-4">
                    <div class="card diet-plan-card h-100">
                        <img src="<?php echo $dietPlan['image_url']; ?>" class="card-img-top" alt="<?php echo $dietPlan['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $dietPlan['name']; ?></h5>
                            <p class="card-text"><?php echo substr($dietPlan['description'], 0, 100) . '...'; ?></p>
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
    </div>
</section>

<!-- Featured Challenges -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Featured Challenges</h2>
            <a href="challenges.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredChallenges as $challenge): ?>
                <div class="col-md-4">
                    <div class="card challenge-card h-100">
                        <img src="<?php echo $challenge['image_url']; ?>" class="card-img-top" alt="<?php echo $challenge['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $challenge['name']; ?></h5>
                            <p class="card-text"><?php echo substr($challenge['description'], 0, 100) . '...'; ?></p>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-<?php 
                                    echo $challenge['difficulty'] === 'Beginner' ? 'success' : 
                                        ($challenge['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
                                ?>"><?php echo $challenge['difficulty']; ?></span>
                                <span class="badge bg-info"><?php echo $challenge['duration']; ?> Days</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-trophy text-warning me-2"></i>
                                <span><?php echo $challenge['points']; ?> Points</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="challenge-detail.php?id=<?php echo $challenge['id']; ?>" class="btn btn-outline-primary w-100">View Challenge</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Join Now Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Start Your Fitness Journey?</h2>
        <p class="lead mb-4">Join thousands of members who have already transformed their lives with FitLife Pro.</p>
        
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-light btn-lg px-4">Join Now</a>
        <?php else: ?>
            <a href="profile.php" class="btn btn-light btn-lg px-4">View Your Profile</a>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Success Stories</h2>
            <p class="lead text-muted">Hear what our members have to say</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <img src="https://images.unsplash.com/photo-1518310383802-640c2de311b2" class="rounded-circle mb-3" alt="Testimonial" width="80" height="80" style="object-fit: cover;">
                        <p class="mb-3">"I've lost 30 pounds and gained so much confidence using FitLife Pro. The exercise tutorials and diet plans are easy to follow and truly effective."</p>
                        <h5 class="card-title mb-1">Michael Roberts</h5>
                        <p class="text-muted">Member since 2021</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <img src="https://images.unsplash.com/photo-1518644961665-ed172691aaa1" class="rounded-circle mb-3" alt="Testimonial" width="80" height="80" style="object-fit: cover;">
                        <p class="mb-3">"The challenges keep me motivated and accountable. I've completed 5 challenges so far and have seen amazing improvements in my strength and endurance."</p>
                        <h5 class="card-title mb-1">Sarah Johnson</h5>
                        <p class="text-muted">Member since 2022</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <img src="https://images.unsplash.com/photo-1483721310020-03333e577078" class="rounded-circle mb-3" alt="Testimonial" width="80" height="80" style="object-fit: cover;">
                        <p class="mb-3">"As a senior, I was worried about finding appropriate exercises. FitLife Pro offers age-specific workouts that have helped me stay active and healthy."</p>
                        <h5 class="card-title mb-1">Robert Williams</h5>
                        <p class="text-muted">Member since 2020</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
