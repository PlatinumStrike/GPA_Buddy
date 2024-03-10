<?php
function redirect($location, $parameters = null)
{
    header("Location: " . $location . ($parameters ? "?" . http_build_query($parameters) : null));
}

// EOF
