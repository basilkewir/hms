// Learn more https://docs.expo.dev/guides/customizing-metro
const { getDefaultConfig } = require('expo/metro-config');
const path = require('path');

/** @type {import('expo/metro-config').MetroConfig} */
const config = getDefaultConfig(__dirname);

// Ensure assets are resolved from the project root
config.projectRoot = __dirname;
config.watchFolders = [__dirname];

// Configure asset extensions
config.resolver.assetExts.push(
  // Add any additional asset extensions if needed
);

module.exports = config;
