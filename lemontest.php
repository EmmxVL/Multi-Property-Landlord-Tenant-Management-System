<!DOCTYPE html>
<html>
<head>
  <title>Supabase Test</title>
</head>
<body>
  <h1>Testing Supabase Connection...</h1>

  <!-- Supabase JS SDK -->
  <script src="https://unpkg.com/@supabase/supabase-js"></script>

  <script>
    // ✅ Replace with your real project details
    const supabaseUrl = 'https://twclndmhuifqjqmgpyfs.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR3Y2xuZG1odWlmcWpxbWdweWZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE1NDkxNjcsImV4cCI6MjA3NzEyNTE2N30.lurgTA4D25yoyCioXCsb7J29Yppa9xa3BqjlnGIoFjg'; // found in Supabase → Settings → API
    const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);

    async function testConnection() {
      const { data, error } = await supabase.from('user_tbl').select('*');
      
      if (error) {
        console.error('Error:', error);
        document.body.innerHTML += `<p style="color:red;">❌ Error: ${error.message}</p>`;
      } else {
        console.log('Data:', data);
        document.body.innerHTML += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
      }
    }

    testConnection();
  </script>
</body>
</html>
