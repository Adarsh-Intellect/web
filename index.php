<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form with Video Recording</title>
</head>
<body>

<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'videodb';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST["name"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $message = $conn->real_escape_string($_POST["message"]);

    if (isset($_POST["videoData"])) {
        // Extract the data content from the data URL
        $dataUrlParts = explode(',', $_POST["videoData"]);

        if (count($dataUrlParts) == 2) {
            $videoData = base64_decode($dataUrlParts[1]);

            // Sanitize the username to create a valid filename
            $sanitizedUsername = preg_replace("/[^a-zA-Z0-9]/", "", $name);

            // Generate a unique filename for the video
            $videoFileName = $sanitizedUsername . '_video.webm';

            // Define the path where videos will be stored
            $uploadDirectory = "D:/xamp/htdocs/Video/uploads/";

            // Save the video data to a file
            $videoFilePath = $uploadDirectory . $videoFileName;
            file_put_contents($videoFilePath, $videoData);

            // Insert data into the database with the video file path
            $sql = "INSERT INTO contact_form_data (name, email, message, video_path) VALUES ('$name', '$email', '$message', '$videoFilePath')";
            if ($conn->query($sql) === TRUE) {
                echo "Data has been inserted into the database.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Invalid video data format.";
        }
    } else {
        echo "Video data not provided.";
    }
}

$conn->close();
?>



<h1>Contact Form with Video Recording</h1>

<form id="contactForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="4" required></textarea><br>

    <label for="video">Live Video Recording:</label>
    <video id="liveVideo" width="400" height="400" autoplay></video>
    <button type="button" id="startRecording">Start Recording</button>
    <button type="button" id="stopRecording" disabled>Stop Recording</button>
    <input type="hidden" id="videoData" name="videoData" required>

    <input type="submit" value="Submit">
</form>

<script>
    const contactForm = document.getElementById('contactForm');
    const liveVideo = document.getElementById('liveVideo');
    const startRecordingButton = document.getElementById('startRecording');
    const stopRecordingButton = document.getElementById('stopRecording');
    const videoDataInput = document.getElementById('videoData');
    let mediaRecorder;
    let recordedChunks = [];

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            liveVideo.srcObject = stream;
            mediaRecorder = new MediaRecorder(stream);

            mediaRecorder.ondataavailable = event => {
                if (event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };

            mediaRecorder.onstop = () => {
                const blob = new Blob(recordedChunks, { type: 'video/webm' });
                const reader = new FileReader();
                
                reader.onloadend = function () {
                    const result = reader.result;
                    videoDataInput.value = result;
                };

                reader.readAsDataURL(blob);
            };
        })
        .catch(error => {
            console.error('Error accessing camera:', error);
        });

    startRecordingButton.addEventListener('click', () => {
        recordedChunks = [];
        mediaRecorder.start();
        startRecordingButton.disabled = true;
        stopRecordingButton.disabled = false;
    });

    stopRecordingButton.addEventListener('click', () => {
        mediaRecorder.stop();
        startRecordingButton.disabled = false;
        stopRecordingButton.disabled = true;
    });
</script>

</body>
</html>
