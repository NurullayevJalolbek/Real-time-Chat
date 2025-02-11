@include('layouts.header')
<div class="max-w-md mx-auto mt-8 bg-white p-8 rounded-md shadow-md">
    <h2 class="text-2xl font-semibold text-center mb-6">Forgot your password?</h2>
    
    <form>
        @csrf
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-2 mt-2 border rounded-md">
        </div>
        
        <div class="mb-4">
            <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded-md">Send Password Reset Link</button>
        </div>
        
        @if (session('status'))
            <div class="text-green-500 text-center mt-4">
                {{ session('status') }}
            </div>
        @endif
    </form>
    <div class="footer">
        <p class="text-white">Already have an account? <a href="{{ route('login') }}"  class="text-blue-400">Login</a></p>
    </div>
</div>