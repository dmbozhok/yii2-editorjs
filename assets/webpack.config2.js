//https://github.com/webpack-contrib/mini-css-extract-plugin
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const devMode = process.env.NODE_ENV !== 'production';

module.exports = {
  optimization: {
    minimizer: [
      new TerserJSPlugin({}),
      new OptimizeCSSAssetsPlugin({})
    ],
  },
  entry: {
    ejs: [
      './src/js/init.js'
    ]
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            query: {
              presets: [ '@babel/preset-env' ],
            },
          },
        ]
      },
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              hmr: process.env.NODE_ENV === 'development',
            },
          },
          'css-loader',
          /*{
            loader: 'postcss-loader',
            options: {
              options: {},
            }
          },*/
		  {
			loader: 'sass-loader',
			options: {
				sourceMap: true
			}
		  }          
        ]
      },
	  {
        test: /\.svg$/,
        loader: 'svg-inline-loader?removeSVGTagAttrs=false'
      }
    ]
  },
  output: {
    path: __dirname + '/dist',
    publicPath: '/',
    filename: 'init.bundle.js',
    library: 'Ejs',
    libraryTarget: 'umd'
  },
  plugins: [
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // all options are optional
      filename: devMode ? '[name].css' : '[name].[hash].css',
      chunkFilename: devMode ? '[id].css' : '[id].[hash].css',
      ignoreOrder: false, // Enable to remove warnings about conflicting order
    }),
  ],
  
};
