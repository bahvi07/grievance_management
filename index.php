<?php include '../complain/includes/header.php'; ?>
<style>
  body.custom-body {
  margin: 0;
  padding: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: rgb(255, 234, 227);
  transition: opacity 1s ease-in-out;
}

img {
  max-width: 100%;
  height: auto;
  border-radius: 50%;
  transition: transform 1s ease-in-out, opacity 1s ease-in-out;
}

.tagline {
  margin-top: 1rem;
  font-size: 1.5rem;
  font-weight: bold;
  color: #FF4500;
  font-family: 'Poppins', sans-serif;
  text-align: center;
}

.fade-out {
  opacity: 0;
  transform: scale(0.9);
}

@media (max-width: 768px) {
  img {
    width: 150px;
    height: 150px;
  }

  .tagline {
    font-size: 1.2rem;
  }
}

</style>
</head>
<body class="custom-body">

  <img src="assets/images/general_images/Bjplogo.jpg" alt="Logo">
  <p class="tagline">Welcome to Vidhayak Seva Kendra</p>

  <script>
     setTimeout(() => {
       document.querySelector('img').classList.add('fade-out');
       document.body.style.opacity = '0';

       setTimeout(() => {
         window.location.href = "./auth/login.php";
     }, 1000);
    }, 2000);
  </script>

</body>
</html>
