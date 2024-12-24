<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tutor Room</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            background-color: #f4f4f9;
            color: #333;
        }

        .video-container {
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }

        .video-container video {
            width: 100%;
            height: auto;
        }

        .video-container h1 {
            margin: 0;
            padding: 16px;
            background-color: #007bff;
            color: white;
            font-size: 1.5em;
        }

        .video-container p {
            margin: 16px;
            font-size: 1em;
            color: #666;
        }

        .footer {
            padding: 8px;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
        }

        .content {
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            max-width: 800px;
            width: 100%;
            text-align: left;
            line-height: 1.6;
        }

        .content h2 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .content p {
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="video-container">
        <h1>Welcome to My Tutor Room</h1>
        <video controls>
            <source src="https://www.mytutorroom.com/static/media/vediotut.1d3f20aaf26441fc2787.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <p>Enjoy the video! Make sure to use a modern browser for the best experience.</p>
        <div class="footer">&copy; 2024 My Tutor Room</div>
    </div>
    <div class="content">
        <h2>Who Are We?</h2>
        <p>My Tutor Room is a privately-owned learning platform. We provide expert online tutoring services for all grade levels from K-12 to university at any time, and anywhere. Our services are convenient for both tutors, students, and institutions. Make no mistake, we guarantee you quality service, transformative learning experience and affordability.</p>
        <h2>OUR VISION</h2>
        <p>To ensure that everyone has access to an affordable learning environment at anytime and anywhere.</p>
    </div>
</body>
</html>
