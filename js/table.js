// Filter sample information boxed based on the filter string
const filterSamples = function() {
  const filterStr = this.value.toLowerCase();
  const sampleBoxes = document.getElementsByClassName("sample-box");

  for (let sampleBox of sampleBoxes) {
    const sampleName = sampleBox.dataset.searchString.toLowerCase();
    if (sampleName.indexOf(filterStr) > -1) {
      sampleBox.style.display = "";
    } else {
      sampleBox.style.display = "none";
    }
  }
};

document.getElementById("sample-filter").addEventListener('keyup', filterSamples);


// Event listener(s) to expand/collapse the sample information boxes
const sampleElems = document.getElementsByClassName("sample-box");

const moreInfoToggle = function() {
  const topElems = this.getElementsByClassName("sample-top");
  if (topElems.length < 1) {
    return;
  }
  const topElem = topElems[0];

  const bottomElems = this.getElementsByClassName("sample-bottom");
  if (bottomElems.length < 1) {
    return;
  }
  const bottomElem = bottomElems[0];

  if (bottomElem.style.display === "") {
    // Show bottom
    bottomElem.style.display = "block";
    // Remove highlight
    for (className of topElem.classList) {
      if (className.includes('highlight')) {
        topElem.classList.remove(className);
        break;
      }
    }
    return;
  }
  if (bottomElem.style.display === "block") {
    // Hide bottom
    bottomElem.style.display = "";
    // Allow highlight
    for (className of topElem.classList) {
      if (className.includes('bg-top-')) {
        topElem.classList.add(className + '-highlight');
        break;
      }
    }
    return;
  }
};

for (var i = 0; i < sampleElems.length; i++) {
  sampleElems[i].addEventListener('dblclick', moreInfoToggle);
  sampleElems[i].addEventListener('mousedown', function(event) {
    if (event.detail > 1) {
      event.preventDefault();
      // of course, you still do not know what you prevent here...
      // You could also check event.ctrlKey/event.shiftKey/event.altKey
      // to not prevent something useful.
      // from:
      // https://stackoverflow.com/questions/880512/prevent-text-selection-after-double-click
    }
  }, false);
}

// Event listener copy sample path
const samplePathCopyElems = document.getElementsByClassName("copy-sample-path");

const copyPath = function() {
  const samplePath = this.dataset.path;
  navigator.clipboard.writeText(samplePath);

  const clipboardElems = this.getElementsByClassName("bi-clipboard");
  if (clipboardElems.length < 1) {
    return;
  }
  clipboardElems[0].classList.add("d-none");

  const clipboardCheckElems = this.getElementsByClassName("bi-clipboard-check");
  if (clipboardCheckElems.length < 1) {
    return;
  }
  clipboardCheckElems[0].classList.remove("d-none");
};

for (var i = 0; i < samplePathCopyElems.length; i++) {
  samplePathCopyElems[i].addEventListener('click', copyPath);
}
