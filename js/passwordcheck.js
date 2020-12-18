document.getElementById("password").onkeyup = function () {
    console.log("[check-started]");
    var password = document.getElementById("password").value;
    if (!password || password.length === 0) {
        console.log("empty password! exiting");
        setStrength();
        return;
    }

    var result = zxcvbn(password);

    if (!result) {
        setStrength();
        return;
    }

    setStrength(result);
}

function setStrength(result = null) {

    var score = 0;
    var message = "";
    var hints = [];
    if (result) {
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

    while (strengthbar.hasChildNodes())
        strengthbar.firstChild.remove();

    if (result) {
        var strengthTitle = document.createElement("P");
        var strengthTitleText = document.createTextNode("Password Strength: ");
        strengthTitle.appendChild(strengthTitleText);
        strengthbar.appendChild(strengthTitle);
        strengthbar.appendChild(document.createElement("SPAN"));
    }

    createMessage(message);
    createHints(hints);
    return;
}

function createMessage(message = "") {
    document.getElementById("password").setCustomValidity(message);
    return;
}

function createHints(hints = []) {
    var listChild = document.getElementById("pwd-strength-hints");
    if (!listChild) {
        listChild = document.createElement("UL");
        listChild.setAttribute("id", "pwd-strength-hints");
        document.getElementById("strength-bar")
            .parentNode
            .insertBefore(
                listChild,
                document
                    .getElementById("strength-bar")
                    .nextSibling
            );
    };
    while (listChild.hasChildNodes()) {
        listChild.firstChild.remove();
    };
    hints.forEach(function (value) {
        createHint(value);
    });

    return;
}

function createHint(hint) {
    var hintElem = document.createElement("LI");
    var hintText = document.createTextNode(hint);
    hintElem.appendChild(hintText);
    document.getElementById("pwd-strength-hints").appendChild(hintElem);
    return
};