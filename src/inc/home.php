<?php

$getStarted = "<h2>Get Started Today!</h2>" .
    "<div><a href='/signup'><button>Sign Up</button></a> " .
    "<a href='/login'><button>Login</button></a></div>";

if (isset($_POST['logout'])) {
    session_unset();
}

if (isset($_SESSION['user_id'])) {
    $getStarted = "<a href='/dashboard'><button>Open Dashboard</button></a> ";
    echo "<div class='flex flex-row justify-between'>" .
        "<p>Signed in to " . $_SESSION['user_email'] . "</p>" .
        "<form action='/' method='post'><input type='submit' name='logout' value='Log Out'></input></form>" .
        "</div>";
}
