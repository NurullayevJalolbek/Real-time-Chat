@include('layouts.header')
    <div class="w-full h-screen flex justify-center items-center">
        <div class="form-container">
            <h2 class="text-2xl font-bold text-white text-center mb-6">Register</h2>
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <!-- Name input field -->
                <input type="text" name="name" class="input-field" placeholder="Ism Familya" value="{{ old('name') }}" required>
                @error('name')
                    <div class="text-red-500 text-sm">{{ $message }}</div>
                @enderror
            
                <!-- Email input field -->
                <input type="email" name="email" class="input-field" placeholder="Email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="text-red-500 text-sm">{{ $message }}</div>
                @enderror
            
                <!-- Password input field -->
                <input type="password" name="password" class="input-field" placeholder="Password" required>
                @error('password')
                    <div class="text-red-500 text-sm">{{ $message }}</div>
                @enderror
            
                <!-- Confirm Password input field -->
                <input type="password" name="password_confirmation" class="input-field" placeholder="Confirm Password" required>
                @error('password_confirmation')
                    <div class="text-red-500 text-sm">{{ $message }}</div>
                @enderror
            
                <!-- Submit button -->
                <button type="submit" class="btn">Register</button>
            </form>
            
            
            <div class="footer">
                <p class="text-white">Already have an account? <a href="{{ route('login') }}"  class="text-blue-400">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
