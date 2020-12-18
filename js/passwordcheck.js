document.getElementById("password").onkeyup = function () {
    console.log("[check-started]");
    var password = document.getElementById("password").value;
    if (!password || password.length === 0) {
        console.log("empty password! exiting");
        setStrength(0);
        return;
    }
    console.log("result: ");

    var result = zxcvbn(password);

    if (!result) {
        console.log("empty result! exiting");
        setStrength(0);
        return;
    }
    console.log(result);
    console.log("score: %d", result.score);
    setStrength(result);


}

function setStrength(result = null) {
    console.log("Set Strength");
    var score = 0;
    var message = "";
    var hints = [];
    if(result){
        score = result.score;
        message = result.feedback.warning;
        hints = result.feedback.suggestions;
    }
        
    var strengthbar = document.getElementById("strength-bar");
    if (!strengthbar)
        return;

    var strength = "pwd-strength-" + score;
    var current_strength = null;

    strengthbar.classList.forEach((value, key, parent) => {
        if (value.match(/^pwd-strength-([0-4])$/g))
            current_strength = value;
    });
    if (current_strength !== null) {
        strengthbar.classList.remove(current_strength);
    }
    strengthbar.classList.add(strength);
    if(result){
    }
    createMessage(message);
    createHints(hints);    
}

function createMessage(message = ""){

    var paragraphChild = document.getElementById("pwd-strength-message");
    if (!paragraphChild) {
        paragraphChild = document.createElement("P");
        console.log(paragraphChild);
        paragraphChild.setAttribute("id", "pwd-strength-message");
        document.getElementById("strength-bar")
            .parentNode
            .insertBefore(
                paragraphChild, 
                document
                    .getElementById("strength-bar")
                    .nextSibling
            );
    };
    while (paragraphChild.hasChildNodes()) {
        paragraphChild.firstChild.remove();
    };
    var messageChild = document.createTextNode(message);
    paragraphChild.appendChild(messageChild);
}

function createHints(hints = []){
    var listChild = document.getElementById("pwd-strength-hints");
    if (!listChild) {
        listChild = document.createElement("UL");
        listChild.setAttribute("id", "pwd-strength-hints");
        document.getElementById("pwd-strength-message")
            .parentNode
            .insertBefore(
                listChild, 
                document
                    .getElementById("pwd-strength-message")
                    .nextSibling
            );
    };
    while (listChild.hasChildNodes()) {
        listChild.firstChild.remove();
    };
    hints.forEach(function (value) {
        createHint(value);
    });
}

function createHint(hint){
   var hintElem = document.createElement("LI");
   var hintText = document.createTextNode(hint);
   hintElem.appendChild(hintText);
   document.getElementById("pwd-strength-hints").appendChild(hintElem);
};