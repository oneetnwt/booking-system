// Create loading screen element
const loadingScreen = document.createElement("div");
loadingScreen.id = "loading-screen";
loadingScreen.innerHTML = `
    <div class="loading-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
`;

// Add loading screen styles
const style = document.createElement("style");
style.textContent = `
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .loading-content {
        text-align: center;
    }
    
    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;

// Function to show loading screen
function showLoadingScreen() {
  console.log("Showing loading screen");
  document.head.appendChild(style);
  document.body.appendChild(loadingScreen);
}

// Function to hide loading screen
function hideLoadingScreen() {
  console.log("Hiding loading screen");
  loadingScreen.style.opacity = "0";
  setTimeout(() => {
    if (loadingScreen.parentNode) {
      loadingScreen.parentNode.removeChild(loadingScreen);
    }
    if (style.parentNode) {
      style.parentNode.removeChild(style);
    }
  }, 500);
}

// Check document ready state
function checkDocumentReady() {
  console.log("Document ready state:", document.readyState);
  if (document.readyState === "loading") {
    console.log("Document is still loading");
    showLoadingScreen();
    document.addEventListener("DOMContentLoaded", hideLoadingScreen);
  } else {
    console.log("Document is already loaded");
    hideLoadingScreen();
  }
}

// Initialize loading screen
console.log("Initializing loading screen");
checkDocumentReady();
