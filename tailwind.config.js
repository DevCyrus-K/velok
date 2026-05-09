export default {
  mode: 'jit',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    screens: {
      xs: '375px',   // Mobile portrait
      sm: '640px',   // Mobile landscape
      md: '768px',   // Tablet
      lg: '1024px',  // Desktop (fixed from 1210px)
      xl: '1280px',  // Wide desktop
      '2xl': '1536px', // Extra large
    },
  },
};
