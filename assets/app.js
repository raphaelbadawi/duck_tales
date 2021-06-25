/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

const quackFileUpload = document.querySelector('[for="quackFileUpload"]');
const quackFileUploadIcon = document.querySelector("#quackFileUploadIcon");
const quackFileUploadInput = document.querySelector("#quackFileUpload");
const quackImagePreview = document.querySelector("#picturePreview");
const quackImagePreviewContainer = document.querySelector(
  "#picturePreviewContainer"
);
const closePicturePreview = document.querySelector("#closePicturePreview");

if (quackFileUpload) {
  quackFileUpload.addEventListener("mouseenter", () => {
    quackFileUploadIcon.classList.add("file-upload-grayscale-icon");
  });

  quackFileUpload.addEventListener("mouseleave", () => {
    quackFileUploadIcon.classList.remove("file-upload-grayscale-icon");
  });

  quackFileUploadInput.addEventListener("change", () => {
    const file = quackFileUploadInput.files[0];
    if (file) {
      const fileReader = new FileReader();
      fileReader.addEventListener("load", () => {
        quackImagePreview.setAttribute("src", fileReader.result);
      });
      fileReader.readAsDataURL(file);
      quackImagePreviewContainer.classList.remove("opacity-0");
    }
  });

  closePicturePreview.addEventListener("click", () => {
    quackImagePreviewContainer.classList.add("opacity-0");
  });
}
