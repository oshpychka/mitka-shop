import path from 'node:path';
import { fileURLToPath } from 'node:url';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import autoprefixer from 'autoprefixer';

const root = path.dirname(fileURLToPath(import.meta.url));

export default (env, argv) => ({
	entry: {
		main: './src/js/main.js',
	},
	output: {
		path: path.resolve(root, 'assets/build'),
		filename: '[name].js',
		chunkFilename: '[name].[contenthash:8].js',
		clean: true,
	},
	devtool: argv.mode === 'development' ? 'source-map' : false,
	module: {
		rules: [
			{
				test: /\.scss$/,
				use: [
					MiniCssExtractPlugin.loader,
					{ loader: 'css-loader', options: { url: false } },
					{ loader: 'postcss-loader', options: { postcssOptions: { plugins: [autoprefixer] } } },
					'sass-loader',
				],
			},
		],
	},
	plugins: [new MiniCssExtractPlugin({ filename: '[name].css' })],
	optimization: {
		minimizer: ['...', new CssMinimizerPlugin()],
	},
	performance: { hints: false },
});
