const btn = document.querySelector("#submitBtn");

btn.addEventListener("click", function (event) {
  this.setAttribute("disabled", "disabled");
  
  this.innerHTML = "Uploading..."

  const form = document.querySelector("#form_id");
  form.submit();

  return true;
});

function disableSubmit(){
  
}
