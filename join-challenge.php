<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please sign in to join challenges.']);
    exit;
}

// Check if challenge ID is provided
if (!isset($_POST['challenge_id']) || !is_numeric($_POST['challenge_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid challenge ID.']);
    exit;
}

$userId = $_SESSION['user_id'];
$challengeId = (int)$_POST['challenge_id'];

// Check if challenge exists
$challenge = getChallengeById($challengeId);
if (!$challenge) {
    echo json_encode(['success' => false, 'message' => 'Challenge not found.']);
    exit;
}

// Join the challenge
if (joinChallenge($userId, $challengeId)) {
    echo json_encode(['success' => true, 'message' => 'You have successfully joined the challenge!']);
} else {
    echo json_encode(['success' => false, 'message' => 'You are already enrolled in this challenge.']);
}
?>
