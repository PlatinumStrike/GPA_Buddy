<?php
session_start();
$getStarted = "<div class='pb-8 pt-4 rounded-xl bg-gray-200'><h2 class='mt-2'>Get Started Today!</h2>" .
    "<div><a href='/signup'><button>Sign Up</button></a> " .
    "<a href='/login'><button>Login</button></a></div></div>";

if (isset($_POST['logout'])) {
    session_unset();
}

$title_class = "";
if (isset($_SESSION['user_id'])) {
    $getStarted = "<a href='/dashboard'><button class='px-8 py-2 rounded-xl'>Open Dashboard</button></a> ";
    echo "<div class='flex flex-row justify-between'>" .
        "<p>Signed in to " . $_SESSION['user_email'] . "</p>" .
        "<form action='/' method='post'><input type='submit' name='logout' value='Log Out'></input></form>" .
        "</div>";
    $title_class = "mt-2";
}

// EOF