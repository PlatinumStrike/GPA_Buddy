<p class="mb-0">Please fill our your MacID and Password</p>
<em class="text-xl">NOTE: These credentials are <b>ONLY</b> used to get transcript and schedule information</em>
<form action="/inc/dashboard.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="form_title" value="upload_transcript">
    <label for="userid">MacID</label>
    <input type="text" id="userid" autocomplete="userid" name="userid" title="User ID">
    <label for="userid">Password</label>
    <input type="password" id="pwd" name="pwd" autocomplete="current-password">
    <br>
    <input type="submit" value="Upload Transcript">
</form>