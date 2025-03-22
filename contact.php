<?php
$pageTitle = 'Contact Us';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$name = $email = $subject = $message = '';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    // Validate form data
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For demonstration purposes, we'll just show a success message
        $success = 'Your message has been sent successfully. We will get back to you as soon as possible.';
        $name = $email = $subject = $message = '';
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-primary text-white text-center py-4 mb-4">
    <h1 class="fw-bold">Contact Us</h1>
    <p class="lead">We'd love to hear from you! Get in touch with our team.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="mb-4">Send Us a Message</h2>
                
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
                
                <form action="contact.php" method="post" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            <div class="invalid-feedback">
                                Please enter your name.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Your Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                        <div class="invalid-feedback">
                            Please enter a subject.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                        <div class="invalid-feedback">
                            Please enter your message.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">
                <h3 class="mb-4">Frequently Asked Questions</h3>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                How do I get started with FitLife Pro?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Getting started is easy! Simply <a href="register.php">create an account</a>, complete your profile with your fitness information, and you'll immediately have access to all our exercises, diet plans, and challenges. We recommend starting with our beginner-friendly content if you're new to fitness.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Are the workouts suitable for all fitness levels?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! We offer exercises and workouts for all fitness levels from beginner to advanced. Our exercises are also categorized by age groups to ensure they're appropriate and safe for everyone. You can filter exercises based on your fitness level and goals.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Can I follow multiple diet plans at once?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We generally recommend focusing on one diet plan at a time for best results. Each plan is designed to be nutritionally complete. However, you can certainly take inspiration from multiple plans if you find certain aspects work better for your lifestyle and preferences.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                How do the challenges work?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Challenges are time-based fitness goals that help keep you motivated. When you join a challenge, you can track your progress daily. As you update your progress, you'll earn points. Once you complete a challenge, you'll earn the full point value and can move on to new challenges.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Is there a mobile app for FitLife Pro?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Our website is fully responsive and works great on mobile devices. We're currently developing a dedicated mobile app for iOS and Android which will be released soon. Stay tuned for updates!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="contact-info p-4 h-100">
            <h3 class="mb-4">Contact Information</h3>
            
            <div class="mb-4">
                <h5><i class="fas fa-map-marker-alt me-2"></i> Our Location</h5>
                <p class="mb-0">123 Fitness Street<br>Health City, HC 10001</p>
            </div>
            
            <div class="mb-4">
                <h5><i class="fas fa-phone me-2"></i> Phone Number</h5>
                <p class="mb-0">(123) 456-7890</p>
                <p class="mb-0">(098) 765-4321</p>
            </div>
            
            <div class="mb-4">
                <h5><i class="fas fa-envelope me-2"></i> Email Address</h5>
                <p class="mb-0">info@fitlifepro.com</p>
                <p class="mb-0">support@fitlifepro.com</p>
            </div>
            
            <div class="mb-4">
                <h5><i class="fas fa-clock me-2"></i> Business Hours</h5>
                <p class="mb-0">Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p class="mb-0">Saturday: 10:00 AM - 4:00 PM</p>
                <p class="mb-0">Sunday: Closed</p>
            </div>
            
            <div>
                <h5><i class="fas fa-share-alt me-2"></i> Follow Us</h5>
                <div class="social-icons d-flex gap-2 mt-2">
                    <a href="#" class="bg-white text-primary"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="bg-white text-primary"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="bg-white text-primary"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="bg-white text-primary"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="bg-white text-primary"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div class="card mt-4 shadow-sm">
    <div class="card-body p-0">
        <div class="ratio ratio-21x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.2241669919664!2d-73.9880399849606!3d40.757977242803004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5e0!3m2!1sen!2sus!4v1618525887325!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
