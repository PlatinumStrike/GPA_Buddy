
document.addEventListener("DOMContentLoaded", function (e) {

    var collapsible = document.getElementsByClassName("collapsible");
    for (var i = 0; i < collapsible.length; i++) {
        var collapsibleElement = collapsible[i];
        collapsibleElement.querySelector(":scope > .collapsible-header").addEventListener("click", function () {
            this.classList.toggle("active");
            var body = collapsibleElement.querySelector(":scope > .collapsible-body");
            if (body.style.maxHeight) {
                body.style.maxHeight = null;
            } else {
                body.style.maxHeight = body.scrollHeight + "px";
            }
        });
    }
});