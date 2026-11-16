const hamburguerMenu = document.querySelector(".hamburguer-menu");
const navbar = document.querySelector(".navbar ul");

hamburguerMenu.addEventListener("click", () => {
  navbar.classList.toggle("active");
});

function toggleMenu() {
  document.querySelector(".navbar").classList.toggle("active");
}

document.addEventListener("click", function (event) {
  const isClickInsideMenu = event.target.closest("header");
  const navbar = document.querySelector(".navbar");

  if (!isClickInsideMenu && navbar.classList.contains("active")) {
    navbar.classList.remove("active");
  }
});

document.addEventListener("scroll", function () {
  const img = document.querySelector(".fade-out-on-scroll");
  const screenWidth = window.innerWidth;

  if (screenWidth < 768) {
    const scrollY = window.scrollY;
    const imgHeight = img.offsetHeight;

    const opacity = 1 - scrollY / imgHeight;
    img.style.opacity = opacity < 0 ? 0 : opacity;
  } else {
    img.style.opacity = 1;
  }
});

window.addEventListener("resize", function () {
  const img = document.querySelector(".fade-out-on-scroll");
  const screenWidth = window.innerWidth;

  if (screenWidth >= 768) {
    img.style.opacity = 1;
  }
});

function closePopup(event) {
  const popup = document.getElementById("popup");
  if (!popup.contains(event.target) || event.type === "click") {
    document.getElementById("popupOverlay").style.display = "none";
  }
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    document.getElementById("popupOverlay").style.display = "none";
  }
});

window.onload = () => {
  document.getElementById("popupOverlay").style.display = "flex";
};
document.addEventListener("DOMContentLoaded", () => {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    const answer = item.querySelector(".faq-answer");
    const toggle = item.querySelector(".faq-toggle");

    question.addEventListener("click", () => {
      const isOpen = answer.classList.contains("open");

      document.querySelectorAll(".faq-answer.open").forEach((openAnswer) => {
        openAnswer.classList.remove("open");
        openAnswer.parentElement.querySelector(".faq-toggle").textContent = "+";
      });

      if (!isOpen) {
        answer.classList.add("open");
        toggle.textContent = "-";
      } else {
        answer.classList.remove("open");
        toggle.textContent = "+";
      }
    });
  });
});


document.addEventListener("DOMContentLoaded", function () {
  const cookiePopupOverlay = document.getElementById("cookiePopupOverlay");
  const floatingCookieButton = document.getElementById("floatingCookieButton");
  const preferences = getCookiePreferences();

  if (preferences.cookiesAccepted) {
    cookiePopupOverlay.style.display = "none";
    floatingCookieButton.style.display = "flex";
    setCheckboxStates(preferences);
  } else {
    cookiePopupOverlay.style.display = "flex";
    floatingCookieButton.style.display = "none";
  }
});

function acceptCookies() {
  const preferences = {
    cookiesAccepted: true,
    performanceCookies: true,
    functionalityCookies: true,
    advertisingCookies: true,
  };

  saveCookiePreferences(preferences);
  updateUIAfterAccept();
}

function savePreferences() {
  const preferences = {
    cookiesAccepted: true,
    performanceCookies: document.getElementById("performanceCookies").checked,
    functionalityCookies: document.getElementById("functionalityCookies").checked,
    advertisingCookies: document.getElementById("advertisingCookies").checked,
  };

  saveCookiePreferences(preferences);
  updateUIAfterSave();
}

function saveCookiePreferences(preferences) {
  localStorage.setItem("cookiePreferences", JSON.stringify(preferences));
}

function getCookiePreferences() {
  const saved = localStorage.getItem("cookiePreferences");
  return saved ? JSON.parse(saved) : {
    cookiesAccepted: false,
    performanceCookies: false,
    functionalityCookies: false,
    advertisingCookies: false,
  };
}

function setCheckboxStates(preferences) {
  document.getElementById("performanceCookies").checked = preferences.performanceCookies;
  document.getElementById("functionalityCookies").checked = preferences.functionalityCookies;
  document.getElementById("advertisingCookies").checked = preferences.advertisingCookies;
}

function manageCookies() {
  const preferences = getCookiePreferences();
  setCheckboxStates(preferences);
  document.getElementById("managePopupOverlay").style.display = "flex";
}

function updateUIAfterAccept() {
  document.getElementById("cookiePopupOverlay").style.display = "none";
  document.getElementById("acceptPopupOverlay").style.display = "flex";
  document.getElementById("floatingCookieButton").style.display = "flex";
  setTimeout(() => {
    document.getElementById("acceptPopupOverlay").style.display = "none";
  }, 2000);
}

function updateUIAfterSave() {
  document.getElementById("managePopupOverlay").style.display = "none";
  document.getElementById("savedPreferencesPopupOverlay").style.display = "flex";
  setTimeout(() => {
    document.getElementById("savedPreferencesPopupOverlay").style.display = "none";
  }, 3000);
}

function closeAcceptPopup() {
  document.getElementById("acceptPopupOverlay").style.display = "none";
}

function closeSavedPreferencesPopup() {
  document.getElementById("savedPreferencesPopupOverlay").style.display = "none";
}

document.getElementById('show-terms').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('popup-overlay').style.display = 'flex';
});

document.getElementById('close-popup').addEventListener('click', function() {
  document.getElementById('popup-overlay').style.display = 'none';
});

document.querySelectorAll('.popup-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      overlay.style.display = 'none';
    }
  });
});



const showTermsLink = document.getElementById("show-terms");
const popupOverlay = document.getElementById("popup-overlay");
const closePopupButton = document.getElementById("close-popup");

showTermsLink.addEventListener("click", (e) => {
  e.preventDefault();
  popupOverlay.style.display = "flex";
});

closePopupButton.addEventListener("click", () => {
  popupOverlay.style.display = "none";
});

popupOverlay.addEventListener("click", (e) => {
  if (e.target === popupOverlay) {
    popupOverlay.style.display = "none";
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    popupOverlay.style.display = "none";
  }
});

setTimeout(() => {
  document.querySelector(".mascot-icon").style.display = "block";
}, 1);

setTimeout(() => {
  document.querySelector(".phone-input").style.display = "block";
}, 1);
