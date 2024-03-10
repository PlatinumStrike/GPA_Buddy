<h1>Dashboard</h1>

<div class="my-20 px-8 py-4 border-2 collapsible">
    <div class="collapsible-header">
        <h2 class="mt-6">Upload Transcript</h2>
    </div>
    <div class="collapsible-body">
        <p>Open <a target="_blank" rel="noopener noreferrer" href="https://mosaic.mcmaster.ca/">MOSAIC</a>, go to Grades, My Academics, View my Course History, then copy and paste the grade table below</p>
        <form action="/inc/dashboard.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_title" value="upload_transcript">
            <label for="incomingTranscript">Paste Transcript here</label>
            <textarea name="incoming_transcript" id="incomingTranscript"></textarea>
            <br>
            <input type="submit" value="Upload Transcript">
        </form>
    </div>
</div>

<?= $course_list ?? "Please upload a transcript" ?>
<br>

<a href="/">Go Home</a>