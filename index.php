<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ToDo Student | Homepage</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero {
            background: #ffffff;
            padding: 60px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .hero h1 {
            font-size: 36px;
            color: #4a5d23;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }

        .hero .btn {
            font-size: 16px;
            padding: 12px 25px;
            margin: 0 10px;
        }

        .features {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .features .card {
            text-align: center;
        }

        .features h4 {
            color: #4a5d23;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- Sidebar (simple for homepage) -->
    <div class="sidebar">
        <h2>ToDo Student</h2>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="about.php">About</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main">

        <div class="hero">
            <h1>Manage Your University Tasks Easily</h1>
            <p>
                ToDo Student helps you organize assignments, discussions, club activities, and exams
                in one professional platform. Plan better, submit on time, and reduce stress.
            </p>
            <a href="register.php" class="btn">Get Started</a>
            <a href="login.php" class="btn">Login</a>
        </div>

        <!-- Features Section -->
        <div class="features">
            <div class="card">
                <h4>Task Recording</h4>
                <p>Add assignments, exams, discussions, and activities with due dates and categories.</p>
            </div>

            <div class="card">
                <h4>Task Monitoring</h4>
                <p>View tasks with filters by category, priority, and due date.</p>
            </div>

            <div class="card">
                <h4>Status Management</h4>
                <p>Mark tasks as On-going, Pending, or Completed.</p>
            </div>

            <div class="card">
                <h4>Task Archiving</h4>
                <p>Completed tasks are archived and can be reviewed anytime.</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>
