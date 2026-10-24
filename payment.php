<?php
session_start();

if(!isset($_SESSION['booking_data'])) {
    header("Location: packages.php");
    exit();
}

$data = $_SESSION['booking_data'];
?>

<?php include 'includes/header.php'; ?>

<div class="container section-padding">
<div class="row justify-content-center">

<div class="col-md-6">

<div class="card p-4">

<h3 class="text-center mb-4">
Select Payment Method
</h3>

<form method="POST"
action="verify.php"
id="paymentForm">

<div class="mb-3 text-center">

<input type="radio"
name="payment_method"
value="card"
checked> Card

<input type="radio"
name="payment_method"
value="upi"
class="ms-3"> UPI

</div>


<!-- ================= CARD SECTION ================= -->

<div id="cardSection">

<div class="mb-3">

<label>Card Number</label>

<input type="text"
name="card_number"
class="form-control"
maxlength="16"
placeholder="1234567890123456">

</div>

<div class="mb-3">

<label>Expiry Date</label>

<input type="text"
name="expiry"
class="form-control"
placeholder="MM/YY"
maxlength="5">

</div>

<div class="mb-3">

<label>CVV</label>

<input type="password"
name="cvv"
class="form-control"
maxlength="3"
placeholder="•••"
autocomplete="off"
id="cvv">

</div>

</div>


<!-- ================= UPI SECTION ================= -->

<div id="upiSection"
style="display:none;">

<div id="upiStep1">

<div class="mb-3">

<label>UPI ID</label>

<input type="text"
name="upi_id"
id="upi_id"
class="form-control"
placeholder="example@upi">

</div>

<button type="button"
class="btn btn-primary w-100"
onclick="goToPin()">

Next

</button>

</div>


<div id="upiStep2"
style="display:none;">

<div class="mb-3 mt-3">

<label>UPI PIN</label>

<input type="password"
name="upi_pin"
id="upi_pin"
class="form-control"
maxlength="6"
placeholder="Enter 4-6 digit PIN">

</div>

</div>

</div>


<button type="submit"
class="btn btn-success w-100 mt-3">

Pay ₹<?php
echo number_format(
$data['total_amount']
);
?>

</button>

</form>

</div>

</div>

</div>

</div>

</div>


<script>

// Toggle Sections

const cardSection =
document.getElementById("cardSection");

const upiSection =
document.getElementById("upiSection");

document
.querySelectorAll(
"input[name='payment_method']"
)

.forEach(el => {

el.addEventListener("change", function () {

if (this.value === "upi") {

upiSection.style.display = "block";
cardSection.style.display = "none";

}

else {

upiSection.style.display = "none";
cardSection.style.display = "block";

}

});

});


// CARD NUMBER VALIDATION

let cardInput =
document.querySelector(
"input[name='card_number']"
);

cardInput
.addEventListener(
"input",
function() {

this.value =
this.value
.replace(/[^0-9]/g, '')
.slice(0,16);

});


// CVV VALIDATION (Hidden + Secure)

let cvvInput =
document.getElementById("cvv");

cvvInput.addEventListener(
"input",
function() {

this.value =
this.value
.replace(/[^0-9]/g, '')
.slice(0,3);

});

// Disable paste in CVV

cvvInput.addEventListener(
"paste",
function(e) {

e.preventDefault();

});


// EXPIRY VALIDATION

let expiryInput =
document.querySelector(
"input[name='expiry']"
);

expiryInput
.addEventListener(
"input",
function() {

let value =
this.value
.replace(/[^0-9]/g, '');

if(value.length >= 3) {

value =
value.slice(0,2)
+ '/'
+ value.slice(2,4);

}

this.value =
value.slice(0,5);

});


// UPI FLOW

function goToPin() {

let upi =
document.getElementById("upi_id").value;

if(!/^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/.test(upi)) {

alert("Enter valid UPI ID!");

return;

}

document
.getElementById("upiStep1")
.style.display = "none";

document
.getElementById("upiStep2")
.style.display = "block";

}


// UPI PIN VALIDATION

let upiPinInput =
document.getElementById("upi_pin");

upiPinInput.addEventListener(
"input",
function() {

this.value =
this.value
.replace(/[^0-9]/g, '')
.slice(0,6);

});


// FINAL SUBMIT VALIDATION

document
.getElementById("paymentForm")
.addEventListener(
"submit",
function(e) {

let method =
document
.querySelector(
"input[name='payment_method']:checked"
).value;

if(method === "card") {

let card =
cardInput.value;

let cvv =
cvvInput.value;

let expiry =
expiryInput.value;

if(!/^[0-9]{16}$/.test(card)) {

alert("Card number must be 16 digits!");

e.preventDefault();
return;

}

if(!/^[0-9]{3}$/.test(cvv)) {

alert("CVV must be 3 digits!");

e.preventDefault();
return;

}

let match =
expiry.match(
/^(0[1-9]|1[0-2])\/([0-9]{2})$/
);

if(!match) {

alert("Invalid expiry date!");

e.preventDefault();
return;

}

}

else {

let pin =
document
.getElementById("upi_pin")
.value;

if(!/^[0-9]{4,6}$/.test(pin)) {

alert("Enter UPI PIN first!");

e.preventDefault();
return;

}

}

});
</script>

<?php include 'includes/footer.php'; ?>