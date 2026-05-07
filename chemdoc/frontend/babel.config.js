const { resolve } = require('path')

module.exports = {
  presets: [
    '@vue/cli-plugin-babel/preset',
    ['@babel/preset-env', { modules: false }]
  ],
  plugins: [],
  resolve: {
    alias: {
      '@': resolve('src')
    }
  }
}
