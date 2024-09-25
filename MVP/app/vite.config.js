import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  base: '/app/',  // Assurez-vous que cela correspond au chemin utilisé dans Nginx
  plugins: [react()],
})
