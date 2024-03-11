<h1>Dashboard</h1>

<br>
<div class="my-20 px-8 py-4 rounded-xl border-2 collapsible">
    <div class="collapsible-header">
        <h2 class="mt-6">Upload Transcript</h2>
        <?= $transcript_upload_date ?>
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

<div class="my-20 px-8 py-4 rounded-xl border-2 collapsible">
    <div class="collapsible-header flex flex-col">
        <h2 class="mt-6">List of Classes</h2>
        <?= $class_list_length ?>
    </div>
    <div class="collapsible-body">
        <?= $class_list ?>
    </div>
</div>

<div class="my-20 px-8 py-4 rounded-xl border-2 collapsible">
    <div class="collapsible-header flex flex-col">
        <h2>GPA Trends</h2>
        <div class="px-8 py-4 border-2">
            <h3>Cummulative GPA</h3>
            <h2 class="mb-0"><?= $cGPALetter ?></h2>
            <h6 class="mt-0">(<?= $cGPA ?>)</h6>
        </div>
        <?= $cGPA ? "<object data='/imgs/down-arrow.svg' class='h-36'></object>" : null ?>
    </div>
    <div class="collapsible-body">
        <div class="my-20 p-0 border-2">
            <div id="gpaTrendGraph"></div>
        </div>
        <div class="my-20 p-0 border-2">
            <div id="gpaPercentTrendGraph"></div>
        </div>
    </div>
</div>


<a href="/">Go Home</a>
<script>
    <?= $script ?>
</script>