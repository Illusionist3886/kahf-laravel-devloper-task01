<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vaccine Status Check</title>
  <link rel="stylesheet" href="{{ asset('assets/app-B9x6yrQY.css') }}">

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <section class="w-full md:w-1/2">
    <div class="my-5 bg-white p-8 rounded-lg shadow-lg w-full">
      <h2 class="text-2xl font-bold text-center mb-6 flex justify-center items-center">
        <img src="{{ asset('/images/vaccine-1dose.png') }}" class="w-6 pr-1" /> 
        COVID-19 Vaccine Status
      </h2>
    
      <div class="mb-4">
        <label for="nid" class="block text-gray-700 font-medium mb-1">NID Number <span class="text-red-500">*</span></label>
        <input type="text" id="nid" name="nid" placeholder="NID Number" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
      </div>

      <div class="flex items-center justify-center">
        <button type="button" id="search" class="flex items-center bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed">
          <span>Search</span>
          <span id="spinner" class="hidden ml-2">
            <svg aria-hidden="true" class="w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
            </svg>
          </span>
        </button>
      </div>

      <div id="userVaccinationStatus" class="mt-4 text-center text-gray-700"></div>
    </div>
  </section>

  <script>
      document.getElementById('search').addEventListener('click', async function(event) {

          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

          let nid = document.getElementById('nid').value;

          if (!nid) {
              document.getElementById('userVaccinationStatus').innerHTML = '<span class="text-red-500">Please enter a valid NID.</span>';
              return;
          }

          const searchButton = document.getElementById('search');
          const spinner = document.getElementById('spinner');
          spinner.classList.remove('hidden');
          searchButton.disabled = true;

          const userVaccinationStatus = document.getElementById('userVaccinationStatus');
          userVaccinationStatus.innerHTML = ''; 

          try {
              const response = await fetch(`/get-status-by-nid?nid=${nid}`, {
                  method: 'GET',
                  headers: {
                      'X-CSRF-TOKEN': csrfToken
                  }
              });

            if (response.status === 404) {
                userVaccinationStatus.innerHTML = `
                    <p class="text-red-500">Status: Not registered</p>
                    <a href="{{ route('registration') }}" class="text-blue-500 underline">Click here to register</a>`;
            } else {

              const result = await response.json();

              if (result.vaccine_status == 'Not Scheduled') {
                  userVaccinationStatus.innerHTML = `<p class="text-yellow-500">Status: Registered but not scheduled for vaccine yet.</p>`;
              } else if (result.vaccine_status == 'Scheduled') {
                  userVaccinationStatus.innerHTML = `<p class="text-green-500">Status: Scheduled for vaccination on ${result.vaccine_schedule?.schedule_date}.</p>`;
              } else if (result.vaccine_status === 'Vaccinated') {
                  userVaccinationStatus.innerHTML = `<p class="text-green-500">Status: ✔️ Vaccinated.</p>`;
              }
            }
              
          } catch (error) {
              document.getElementById('userVaccinationStatus').innerHTML = `<span class="text-red-500">Error fetching status. Please try again later.</span>`;
          }  finally {
            spinner.classList.add('hidden');
            searchButton.disabled = false;
          }
      });
  </script>
</body>
</html>