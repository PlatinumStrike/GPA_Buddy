
document.addEventListener("DOMContentLoaded", function (e) {
    // Select all collapsible containers
    var collapsible = document.getElementsByClassName("collapsible");

    // Loop thorugh each container
    for (var i = 0; i < collapsible.length; i++) {

        // Mark container and add click listener to all header children
        var collapsibleElement = collapsible[i];
        collapsibleElement.querySelector(":scope > .collapsible-header").addEventListener("click", function () {

            // Rescope container
            var collapsibleElement = this.parentNode;

            // Toggle active flag on container
            this.classList.toggle("active");

            // Select body
            var body = collapsibleElement.querySelector(":scope > .collapsible-body");

            // Adjust body height based on state
            if (body.style.height) {
                body.style.height = null;
            } else {
                body.style.height = body.scrollHeight + "px";
            }


            // Handle nested container case
            if (collapsibleElement.parentNode.parentNode.classList.contains("collapsible")) {
                // Adjust parent size based on body scrollHeight
                var parent_collapsible = collapsibleElement.parentNode;
                parent_collapsible.style.height = (parseInt(parent_collapsible.style.height.slice(0, -2)) + (parseInt(body.style.height.slice(0, -2)) || - body.scrollHeight)).toString() + "px"
            }
        });
    }
});