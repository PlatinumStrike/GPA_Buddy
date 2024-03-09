<h1>Login to your GPA Buddy</h1>

<form action="/inc/login.php" method="post">

    <label for="email">Email:</label>
    <input id="email" name="email" type="email">

    <label for="pwd">Password:</label>
    <input id="pwd" name="pwd" type="password">

    <div class="flex items-baseline justify-center">
        <label for="persist" class="px-4">Stay Signed In?</label>
        <input id="persist" name="persist" type="checkbox">
    </div>

    <br>

    <input value="Log In" type="submit">

</form>

<p>Haven't made an account yet? <a href="/signup">Sign Up</a></p>

<br>

<a href="/">Go Home</a>