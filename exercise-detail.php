<?php
$pageTitle = 'Exercise Details';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid exercise ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: exercises.php');
    exit;
}

$exerciseId = (int)$_GET['id'];

// Get exercise details
$exercise = getExerciseById($exerciseId);

if (!$exercise) {
    $_SESSION['message'] = 'Exercise not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: exercises.php');
    exit;
}

// Get similar exercises
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT * FROM exercises 
        WHERE target_muscle = ? 
        AND id != ? 
        ORDER BY RAND() 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $exercise['target_muscle'], $exerciseId);
$stmt->execute();
$similarExercises = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Add extra scripts
$extraScripts = ['assets/js/exercise.js'];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <div class="container">
        <h1 class="fw-bold"><?php echo htmlspecialchars($exercise['name']); ?></h1>
        <div class="d-flex justify-content-center gap-3 mt-2">
            <span class="badge bg-<?php 
                echo $exercise['difficulty'] === 'Beginner' ? 'success' : 
                    ($exercise['difficulty'] === 'Intermediate' ? 'warning' : 'danger'); 
            ?> fs-6"><?php echo htmlspecialchars($exercise['difficulty']); ?></span>
            <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($exercise['target_muscle']); ?></span>
            <span class="badge bg-info fs-6"><?php echo htmlspecialchars($exercise['category']); ?></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 shadow-sm">
            <div class="row g-0">
                <div class="col-md-6">
                    <?php if (!empty($exercise['gif_url'])): ?>
                        <img src="<?php echo htmlspecialchars($exercise['gif_url']); ?>" class="exercise-detail-img w-100" alt="<?php echo htmlspecialchars($exercise['name']); ?> demonstration">
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($exercise['image_url']); ?>" class="exercise-detail-img w-100" alt="<?php echo htmlspecialchars($exercise['name']); ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="card-body">
                        <h2 class="card-title mb-3"><?php echo htmlspecialchars($exercise['name']); ?></h2>
                        <p class="card-text"><?php echo htmlspecialchars($exercise['description']); ?></p>
                        
                        <h5 class="mt-4 mb-3">Exercise Details</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><i class="fas fa-dumbbell me-2 text-primary"></i> Type:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($exercise['category']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><i class="fas fa-muscle me-2 text-primary"></i> Target Muscle:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($exercise['target_muscle']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><i class="fas fa-signal me-2 text-primary"></i> Difficulty:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($exercise['difficulty']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><i class="fas fa-user me-2 text-primary"></i> Age Range:</span>
                                <span class="fw-bold"><?php echo $exercise['min_age'] . '-' . $exercise['max_age']; ?> years</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">How to Perform</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="mb-3">Proper Form</h5>
                <ol>
                    <?php
                    // Generate steps based on exercise type
                    $steps = [];
                    switch($exercise['category']) {
                        case 'Strength':
                            $steps = [
                                "Start with the proper stance and grip for this exercise.",
                                "Maintain good posture throughout the movement.",
                                "Engage your core muscles for stability.",
                                "Perform the movement with controlled motion.",
                                "Breathe properly - exhale during exertion, inhale during the return phase."
                            ];
                            break;
                        case 'Cardio':
                            $steps = [
                                "Start with a proper warm-up to prepare your body.",
                                "Begin at a moderate pace to establish rhythm.",
                                "Maintain good form throughout the exercise.",
                                "Focus on consistent breathing patterns.",
                                "Gradually increase intensity as appropriate for your fitness level."
                            ];
                            break;
                        case 'Core':
                            $steps = [
                                "Start in the correct position with your core engaged.",
                                "Maintain proper alignment throughout the exercise.",
                                "Focus on controlled movements rather than speed.",
                                "Breathe steadily throughout the exercise.",
                                "Gradually increase duration as your core strength improves."
                            ];
                            break;
                        case 'Plyometric':
                            $steps = [
                                "Begin with a proper warm-up to prepare your muscles and joints.",
                                "Start from a stable position.",
                                "Focus on explosive movements with controlled landings.",
                                "Maintain proper form throughout the exercise.",
                                "Allow for adequate rest between sets."
                            ];
                            break;
                        default:
                            $steps = [
                                "Start in the proper position for this exercise.",
                                "Focus on correct form throughout the movement.",
                                "Control the tempo of the exercise.",
                                "Breathe properly throughout the movement.",
                                "Complete the full range of motion for maximum benefit."
                            ];
                    }
                    
                    foreach($steps as $step):
                    ?>
                        <li class="mb-2"><?php echo $step; ?></li>
                    <?php endforeach; ?>
                </ol>
                
                <h5 class="mt-4 mb-3">Common Mistakes to Avoid</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>Poor form:</strong> Always prioritize proper technique over weight or repetitions.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>Rushing through movements:</strong> Control the tempo for better muscle engagement.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>Holding your breath:</strong> Maintain proper breathing throughout the exercise.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>Using momentum:</strong> Let your muscles do the work, not momentum.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <strong>Skipping warm-up:</strong> Always warm up properly to prevent injury.
                    </li>
                </ul>
                
                <h5 class="mt-4 mb-3">Recommended Sets and Repetitions</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Experience Level</th>
                                <th>Sets</th>
                                <th>Repetitions</th>
                                <th>Rest Between Sets</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Beginner</td>
                                <td>2-3</td>
                                <td>10-12</td>
                                <td>60-90 seconds</td>
                            </tr>
                            <tr>
                                <td>Intermediate</td>
                                <td>3-4</td>
                                <td>8-10</td>
                                <td>45-60 seconds</td>
                            </tr>
                            <tr>
                                <td>Advanced</td>
                                <td>4-5</td>
                                <td>6-8</td>
                                <td>30-45 seconds</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <?php if (isLoggedIn()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Log Your Workout</h4>
                </div>
                <div class="card-body p-4">
                    <form id="logExerciseForm" action="log-exercise.php" method="post" data-exercise-id="<?php echo $exercise['id']; ?>">
                        <div class="mb-3">
                            <label for="workout_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="workout_date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="sets" class="form-label">Sets</label>
                                <input type="number" class="form-control" id="sets" name="sets" min="1" value="3">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="reps" class="form-label">Reps</label>
                                <input type="number" class="form-control" id="reps" name="reps" min="1" value="10">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight" min="0" step="0.5" value="0">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="duration" class="form-label">Duration (min)</label>
                                <input type="number" class="form-control" id="duration" name="duration" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="How did it feel? Any modifications?"></textarea>
                        </div>
                        
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="exercise_id" value="<?php echo $exercise['id']; ?>">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Log Exercise</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Track Your Progress</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <i class="fas fa-user-circle fa-3x text-muted mb-3"></i>
                    <h5>Sign In to Log Workouts</h5>
                    <p>Keep track of your sets, reps, and progress over time.</p>
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary">Sign In</a>
                        <a href="register.php" class="btn btn-outline-secondary">Create Account</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Similar Exercises</h4>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($similarExercises)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($similarExercises as $similarExercise): ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($similarExercise['image_url']); ?>" class="rounded me-3" width="60" height="60" style="object-fit: cover;" alt="<?php echo htmlspecialchars($similarExercise['name']); ?>">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($similarExercise['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($similarExercise['difficulty']); ?></small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="exercise-detail.php?id=<?php echo $similarExercise['id']; ?>" class="btn btn-sm btn-outline-primary">View Exercise</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="p-3 text-center">
                        <p class="mb-0">No similar exercises found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Tips for Best Results</h4>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Proper warm-up:</strong> Always warm up before starting this exercise.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Consistent practice:</strong> Aim to include this exercise in your routine 2-3 times per week.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Progressive overload:</strong> Gradually increase intensity as you get stronger.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Rest and recovery:</strong> Allow 48 hours for muscle recovery between sessions.
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Nutrition:</strong> Support your training with proper nutrition and hydration.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
