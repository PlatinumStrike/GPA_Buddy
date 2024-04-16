<h1>Dashboard</h1>

<br>
<div class="my-20 px-8 py-4 rounded-xl border-2 collapsible">
    <div class="collapsible-header justify-center justify-items-center grid">
        <h2 class="mt-6">Upload Transcript</h2>
        <?= $transcript_upload_date ?>
        <?= $cGPA ? "<object data='/imgs/down-arrow.svg' class='h-36 svg_obj'><style>.svg_obj {pointer-events: none;}</style></object>" : null ?>
    </div>
    <div class="collapsible-body">
        <p class="mb-0">Please fill our your MacID and Password to upload your transcript</p>
        <em class="text-xl">NOTE: These credentials are not stored and are requried on every upload</em>
        <form action="/inc/dashboard.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="form_title" value="upload_transcript">
            <label for="userid">MacID</label>
            <input type="text" id="userid" autocomplete="userid" name="userid" title="User ID">
            <label for="userid">Password</label>
            <input type="password" id="pwd" name="pwd" autocomplete="current-password">
            <br>
            <input type="submit" value="Upload Transcript">
        </form>
    </div>
</div>

<div class="my-20 px-8 py-4 rounded-xl border-2 collapsible">
    <div class="collapsible-header flex flex-col">
        <h2 class="mt-6">List of Classes</h2>
        <?= $class_list_length ?>
        <?= $cGPA ? "<object data='/imgs/down-arrow.svg' class='h-36 svg_obj'><style>.svg_obj {pointer-events: none;}</style></object>" : null ?>
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
        <?= $cGPA ? "<object data='/imgs/down-arrow.svg' class='h-36 svg_obj'><style>.svg_obj {pointer-events: none;}</style></object>" : null ?>
    </div>
    <div class="collapsible-body">
        <div class="my-20 p-0 border-2">
            <div id="gpaTrendGraph"></div>
        </div>
        <div class="my-20 p-0 border-2">
            <div id="gpaPercentTrendGraph"></div>
        </div>
        <div class="my-20 p-0 border-2">
            <div id="unitTrendGraph"></div>
        </div>
    </div>
</div>


<a href="/">Go Home</a>
<script>
    <?= $script ?>
</script>