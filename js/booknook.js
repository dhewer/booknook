
// BookNook Custom Scripts

jQuery(document).ready(function(){

    // Automatically display the hex color code in meta box
    let booknook_color = document.querySelector("#booknook_color");
    let booknook_textcolor = document.querySelector("#booknook_textcolor");
    if (booknook_color) {
        booknook_color.addEventListener("change", updateBooknookColor, false);
        booknook_textcolor.addEventListener("change", updateBooknookColor, false);
        function updateBooknookColor(e) {
            let display = document.querySelector(".booknook-color-display");
            display.innerHTML = booknook_textcolor.value;
            display.style.backgroundColor = booknook_color.value;
            display.style.color = booknook_textcolor.value;
        }
    }

    // Copy shortcode to clipboard when Copy button is clicked
    let booknook_shortcodeCopy = document.querySelector(".booknook-shortcodeCopy");
    let booknook_copyButton = document.querySelector(".booknook-copyButton");
    if (booknook_shortcodeCopy) {
        booknook_copyButton.addEventListener("click", copyBooknookShortcode, false);
        function copyBooknookShortcode(e) {
            e.preventDefault();
            booknook_shortcodeCopy.select();
            booknook_shortcodeCopy.setSelectionRange(0, 20);
            navigator.clipboard.writeText(booknook_shortcodeCopy.value);
            booknook_copyButton.innerHTML = 'COPIED!';
            setTimeout(function(){
                booknook_copyButton.innerHTML = 'Copy';
            }, 2000);
        }
    }

});
