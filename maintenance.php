<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | Flavour's Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .maint-container {
            text-align: center;
            padding: 50px;
            background: rgba(255, 78, 0, 0.05);
            border: 1px solid rgba(255, 78, 0, 0.2);
            border-radius: 40px;
            backdrop-filter: blur(20px);
            max-width: 600px;
            animation: pulse-border 3s infinite ease-in-out;
        }
        @keyframes pulse-border {
            0%, 100% { border-color: rgba(255, 78, 0, 0.2); box-shadow: 0 0 20px rgba(255, 78, 0, 0.1); }
            50% { border-color: rgba(255, 78, 0, 0.5); box-shadow: 0 0 40px rgba(255, 78, 0, 0.3); }
        }
        .icon {
            font-size: 5rem;
            color: #ff4e00;
            margin-bottom: 30px;
            animation: rotate-cog 5s infinite linear;
        }
        @keyframes rotate-cog { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        h1 { font-size: 3rem; font-weight: 900; margin: 0; letter-spacing: -2px; }
        p { color: #888; font-size: 1.2rem; line-height: 1.6; margin-top: 20px; }
        .back-soon {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #ff4e00;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="maint-container">
        <div class="icon"><i class="fas fa-tools"></i></div>
        <h1>UNDER MAINTENANCE</h1>
        <p>The culinary sparks are flying! We're upgrading our heat to serve you better. Please come back in a few moments.</p>
        <span class="back-soon">BACK SOON</span>
    </div>
</body>
</html>
