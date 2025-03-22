from flask import Flask, render_template, request, redirect, url_for, flash, session
import os
import datetime

app = Flask(__name__)
app.secret_key = os.environ.get("SESSION_SECRET", "fitlifepro_secret_key")

# Mock user data for demonstration
users = {
    1: {"id": 1, "username": "demo_user", "email": "user@example.com", "password": "password"}
}
# Next user ID to be used for new registrations
next_user_id = 2

# Mock exercise data
exercises = [
    {
        "id": 1,
        "name": "Push-ups",
        "description": "A classic bodyweight exercise that targets the chest, shoulders, and triceps.",
        "difficulty": "Beginner",
        "target_muscle": "Chest",
        "category": "Strength",
        "image_url": "https://cdn.pixabay.com/photo/2018/02/04/21/13/fitness-3130503_960_720.jpg",
        "gif_url": "",
        "min_age": 12,
        "max_age": 70
    },
    {
        "id": 2,
        "name": "Squats",
        "description": "A fundamental lower body exercise that targets the quadriceps, hamstrings, and glutes.",
        "difficulty": "Beginner",
        "target_muscle": "Legs",
        "category": "Strength",
        "image_url": "https://cdn.pixabay.com/photo/2018/04/05/17/21/kettlebell-3293475_960_720.jpg",
        "gif_url": "",
        "min_age": 12,
        "max_age": 70
    },
    {
        "id": 3,
        "name": "Planks",
        "description": "An isometric core exercise that strengthens the abdominals, back, and shoulders.",
        "difficulty": "Beginner",
        "target_muscle": "Core",
        "category": "Core",
        "image_url": "https://cdn.pixabay.com/photo/2018/04/04/16/44/kettlebell-3290296_960_720.jpg",
        "gif_url": "",
        "min_age": 12,
        "max_age": 70
    }
]

# Mock diet plans
diet_plans = [
    {
        "id": 1,
        "name": "Balanced Nutrition Plan",
        "description": "A well-rounded diet with balanced macronutrients for general health and fitness.",
        "category": "Balanced",
        "calories": 2000,
        "protein": 100,
        "carbs": 200,
        "fat": 67,
        "image_url": "https://cdn.pixabay.com/photo/2017/06/21/22/42/vegetables-2428546_960_720.jpg"
    },
    {
        "id": 2,
        "name": "Keto Diet Plan",
        "description": "A high-fat, low-carb diet designed to induce ketosis for fat burning.",
        "category": "Keto",
        "calories": 1800,
        "protein": 120,
        "carbs": 25,
        "fat": 130,
        "image_url": "https://cdn.pixabay.com/photo/2016/03/05/19/02/salmon-1238248_960_720.jpg"
    },
    {
        "id": 3,
        "name": "Mediterranean Diet",
        "description": "Based on the traditional foods of Mediterranean countries, emphasizing plant foods, olive oil, and fish.",
        "category": "Mediterranean",
        "calories": 2200,
        "protein": 90,
        "carbs": 250,
        "fat": 70,
        "image_url": "https://cdn.pixabay.com/photo/2016/08/09/19/30/tomatoes-1581913_960_720.jpg"
    }
]

# Mock challenges
challenges = [
    {
        "id": 1,
        "name": "30-Day Push-up Challenge",
        "description": "Increase your upper body strength with daily push-ups for 30 days.",
        "difficulty": "Beginner",
        "duration": 30,
        "category": "Strength",
        "image_url": "https://cdn.pixabay.com/photo/2015/07/02/10/22/training-828726_960_720.jpg",
        "point_value": 500
    },
    {
        "id": 2,
        "name": "Couch to 5K",
        "description": "A progressive running program designed to get beginners from the couch to running 5K in 8 weeks.",
        "difficulty": "Beginner",
        "duration": 56,
        "category": "Cardio",
        "image_url": "https://cdn.pixabay.com/photo/2016/11/14/03/35/runner-1822459_960_720.jpg",
        "point_value": 800
    },
    {
        "id": 3,
        "name": "21-Day Plank Challenge",
        "description": "Build core strength by increasing your plank time each day for 21 days.",
        "difficulty": "Intermediate",
        "duration": 21,
        "category": "Core",
        "image_url": "https://cdn.pixabay.com/photo/2017/08/07/14/02/man-2604149_960_720.jpg",
        "point_value": 400
    }
]

# Helper functions
def get_exercise_by_id(exercise_id):
    for exercise in exercises:
        if exercise["id"] == exercise_id:
            return exercise
    return None

def get_diet_plan_by_id(diet_plan_id):
    for diet_plan in diet_plans:
        if diet_plan["id"] == diet_plan_id:
            return diet_plan
    return None

def get_challenge_by_id(challenge_id):
    for challenge in challenges:
        if challenge["id"] == challenge_id:
            return challenge
    return None

# Routes
@app.route('/')
def index():
    return render_template('index.html', featured_exercises=exercises[:3], 
                          featured_diet_plans=diet_plans[:3], 
                          featured_challenges=challenges[:3])

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')
        
        # Simple mock authentication
        for user_id, user in users.items():
            if user['email'] == email and user['password'] == password:
                session['user_id'] = user_id
                flash('You have successfully logged in!', 'success')
                return redirect(url_for('index'))
        
        flash('Invalid email or password. Please try again.', 'danger')
    
    return render_template('login.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        # Get form data
        username = request.form.get('username')
        email = request.form.get('email')
        password = request.form.get('password')
        age = request.form.get('age')
        fitness_level = request.form.get('fitness_level')
        
        # Check if email already exists
        for user in users.values():
            if user['email'] == email:
                flash('Email already registered. Please use a different email or log in.', 'danger')
                return redirect(url_for('register'))
        
        # In a real app, you would save the user data to the database
        global next_user_id
        users[next_user_id] = {
            "id": next_user_id,
            "username": username,
            "email": email,
            "password": password,
            "age": age,
            "fitness_level": fitness_level
        }
        
        # Auto-login the user after registration
        session['user_id'] = next_user_id
        next_user_id += 1
        
        flash('Account created successfully! You are now logged in.', 'success')
        return redirect(url_for('index'))
    
    return render_template('register.html')

@app.route('/logout')
def logout():
    session.pop('user_id', None)
    flash('You have been logged out.', 'success')
    return redirect(url_for('index'))

@app.route('/profile')
def profile():
    if 'user_id' not in session:
        flash('Please log in to view your profile.', 'warning')
        return redirect(url_for('login'))
    
    user = users.get(session['user_id'])
    return render_template('profile.html', user=user)

@app.route('/exercises')
def exercises_list():
    return render_template('exercises.html', exercises=exercises)

@app.route('/exercise/<int:exercise_id>', methods=['GET', 'POST'])
def exercise_detail(exercise_id):
    exercise = get_exercise_by_id(exercise_id)
    if not exercise:
        flash('Exercise not found.', 'danger')
        return redirect(url_for('exercises_list'))
    
    # Handle workout logging (POST request)
    if request.method == 'POST' and 'user_id' in session:
        # In a real app, you would save the workout data to a database
        flash('Workout logged successfully!', 'success')
        return redirect(url_for('exercise_detail', exercise_id=exercise_id))
    
    # Get current date for the date input default value
    today_date = datetime.date.today().strftime('%Y-%m-%d')
    
    similar_exercises = [ex for ex in exercises if ex['target_muscle'] == exercise['target_muscle'] and ex['id'] != exercise_id][:3]
    return render_template('exercise_detail.html', exercise=exercise, similar_exercises=similar_exercises, today_date=today_date)

@app.route('/diet-plans')
def diet_plans_list():
    return render_template('diet_plans.html', diet_plans=diet_plans)

@app.route('/diet-plan/<int:diet_plan_id>')
def diet_plan_detail(diet_plan_id):
    diet_plan = get_diet_plan_by_id(diet_plan_id)
    if not diet_plan:
        flash('Diet plan not found.', 'danger')
        return redirect(url_for('diet_plans_list'))
    
    similar_diet_plans = [dp for dp in diet_plans if dp['category'] == diet_plan['category'] and dp['id'] != diet_plan_id][:3]
    return render_template('diet_plan_detail.html', diet_plan=diet_plan, similar_diet_plans=similar_diet_plans)

@app.route('/challenges')
def challenges_list():
    return render_template('challenges.html', challenges=challenges)

@app.route('/challenge/<int:challenge_id>')
def challenge_detail(challenge_id):
    challenge = get_challenge_by_id(challenge_id)
    if not challenge:
        flash('Challenge not found.', 'danger')
        return redirect(url_for('challenges_list'))
    
    similar_challenges = [ch for ch in challenges if ch['category'] == challenge['category'] and ch['id'] != challenge_id][:3]
    return render_template('challenge_detail.html', challenge=challenge, similar_challenges=similar_challenges)

@app.route('/contact', methods=['GET', 'POST'])
def contact():
    if request.method == 'POST':
        # In a real app, you would send the message
        flash('Your message has been sent successfully. We will get back to you as soon as possible.', 'success')
        return redirect(url_for('contact'))
    
    return render_template('contact.html')

@app.route('/join-challenge/<int:challenge_id>', methods=['POST'])
def join_challenge(challenge_id):
    if 'user_id' not in session:
        flash('Please log in to join challenges.', 'warning')
        return redirect(url_for('login'))
    
    challenge = get_challenge_by_id(challenge_id)
    if not challenge:
        flash('Challenge not found.', 'danger')
        return redirect(url_for('challenges_list'))
    
    flash(f'You have successfully joined the {challenge["name"]}!', 'success')
    return redirect(url_for('challenge_detail', challenge_id=challenge_id))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)