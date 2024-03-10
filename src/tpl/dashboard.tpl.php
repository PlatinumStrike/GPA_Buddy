<h1>Dashboard</h1>

<form action="/inc/dashboard.php" method="POST" enctype="multipart/form-data" class="my-20 px-8 py-4 border-2">
    <input type="hidden" name="form_title" value="upload_transcript">
    <h2 class="mt-6">Upload Transcript</h2>
    <p>Open <a href="https://mosaic.mcmaster.ca/">MOSAIC</a>, go to Grades, My Academics, View my Course History, then copy and paste the table presented into here:</p>
    <textarea name="incoming_transcript" id="incomingTranscript"></textarea>
    <br>
    <input type="submit" value="Upload Transcript">
</form>

<?= $transcript_table ?? null ?>
<br>

<a href="/">Go Home</a>