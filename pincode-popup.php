<?php
/**
 * Plugin Name: Pincode Popup
 * Description: Shows a pincode popup until pincode is set in cookies.
 * Version: 1.0
 * Text Domain: pincode-popup
 */

// Shortcode [pincode-popup]
add_shortcode('pincode-popup', function(){
  if (isset($_COOKIE['pincode_verified'])) {
  // Do not show the popup if cookie is already set
        return '';
    }
ob_start();?>

<div id="pincode-popup">
  <div class="pincode-box">
    <h2>Enter PIN Code</h2>
      <p>Enter PIN code to see product availability, offers, and discounts.</p>

    <div class="input-wrapper">
      <span class="location-icon">
        <img src="https://www.jiomart.com/assets/ds2web/jds-icons/location-gray-icon.svg" alt="">
      </span>
      <input id="pincode_input" type="text" placeholder="PIN Code">
      <button id="pincode_submit">Apply</button>
    </div>
  </div>
</div>

<style>
  #pincode-popup{
    position:fixed;
    top:0; left:0; bottom:0; right:0;
    background:rgba(0,0,0,0.7);
    z-index:9999;
    color:#fff;
    display:flex;
    align-items:flex-start;
    justify-content:flex-start;
  }

  .pincode-box {
    position: relative;
    background: #fff;
    color: #000;
    padding: 30px 20px;
    border-radius: 20px;
    width: 500px;
    /* text-align: center; */
    box-shadow: 0 4px 14px rgb(0 0 0 / 0.5);
    margin: 50px 0 0 50px;  /* <- move toward top left */
  }

  .pincode-box::after {
    content:'';
    position: absolute;
    bottom: 100%;
    left: 20px;
    border-bottom: 20px solid #fff;
    border-right: 20px solid transparent;
    border-left: 20px solid transparent;
  }



  .pincode-box h2 {
    margin-bottom: 20px;
  }

  .pincode-box p {
    color: #555;
    margin-bottom: 20px;
  }

  .input-wrapper {
    position: relative;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }

  .location-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
  }

  .input-wrapper input {
    width: 100%;
    padding: 10px 10px 10px 40px;
    border-radius: 12px;
    border: 1px solid #ccc;
    outline: none;
    font-size: 16px;
  }

  .input-wrapper button {
    /* margin-top: 15px; */
    padding: 10px 20px;
    background: #007bff;
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .input-wrapper button:hover {
    background: #0056b3;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded',function(){
    if (document.cookie.indexOf('pincode_verified')===-1){
      document.getElementById('pincode-popup').style.display='flex';
    } else {
      document.getElementById('pincode-popup').style.display='none';
    }

    document.getElementById('pincode_submit').addEventListener('click',(e)=>{
      e.preventDefault();
      var pincode = document.getElementById('pincode_input').value.trim();

      if (pincode.length===6) {
        document.cookie = "pincode_verified="+pincode+";path=/;max-age=2592000";
        document.getElementById('pincode-popup').style.display='none';
        location.reload();
      } else {
        alert('Invalid pincode.');
      }
    });
  });
</script>
    <?php
    return ob_get_clean();
});