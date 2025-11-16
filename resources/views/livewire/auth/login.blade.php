<div style="max-width: 400px; margin: 100px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <h2 style="text-align: center; margin-bottom: 20px;">Login SIKOPMA</h2>
    
    @if($errorMessage)
        <div style="color: red; margin-bottom: 15px; padding: 10px; background: #ffe6e6; border: 1px solid red;">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="login">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">NIM:</label>
            <input 
                type="text" 
                wire:model="nim" 
                style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
                placeholder="Masukkan NIM"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Password:</label>
            <input 
                type="password" 
                wire:model="password" 
                style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
                placeholder="Masukkan password"
            >
        </div>

        <div style="margin-bottom: 20px;">
            <label>
                <input type="checkbox" wire:model="remember"> Ingat saya
            </label>
        </div>

        <button 
            type="submit" 
            style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;"
        >
            Masuk
        </button>
    </form>
    
    <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; font-size: 12px;">
        <strong>Test Account:</strong><br>
        NIM: 00000000<br>
        Password: password
    </div>
</div>
