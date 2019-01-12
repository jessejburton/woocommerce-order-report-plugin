const path = require('path');
const distPath = path.join(__dirname, 'public');
const webpack = require('webpack');

module.exports = () => {
  return {
    entry: ['./src/js/script.js', './src/sass/styles.scss'],
    output: {
      path: distPath,
      filename: 'js/script.js'
    },
    module: {
      rules: [
        {
          loader: 'babel-loader',
          test: /\.js$/,
          exclude: /node_modules/
        },
        {
          test: /\.s?css$/,
          use: [
            {
              loader: 'file-loader',
              options: {
                name: './css/styles.css'
              }
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: true
              }
            }
          ]
        }
      ]
    },
    devtool: 'source-map'
  };
};
