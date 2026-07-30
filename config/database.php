<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans p-6">

    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">Hostel Management</h1>
            <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full">Dashboard</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-lg font-bold text-gray-700 mb-4">Book a Room</h2>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Student Name</label>
                        <input type="text" name="student_name" required placeholder="John Doe" class="w-full border rounded p-2 text-sm focus:outline-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Room Number</label>
                        <input type="text" name="room_number" required placeholder="A-101" class="w-full border rounded p-2 text-sm focus:outline-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Contact</label>
                        <input type="text" name="contact" required placeholder="+123456789" class="w-full border rounded p-2 text-sm focus:outline-indigo-500">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded transition">
                        Book Room
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-sm">
                <h2 class="text-lg font-bold text-gray-700 mb-4">Current Residents</h2>

                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-gray-500 uppercase text-xs">
                            <th class="pb-3">ID</th>
                            <th class="pb-3">Name</th>
                            <th class="pb-3">Room</th>
                            <th class="pb-3">Contact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-400">No residents found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
