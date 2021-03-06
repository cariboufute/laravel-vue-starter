module.exports = {
    clearMocks: true,
    coverageDirectory: "coverage",
    moduleFileExtensions: [
        "js",
        "json",
        "vue",
    ],

    transform: {
        ".*\\.(js)$": "babel-jest",
        ".*\\.(vue)$": "@vue/vue3-jest"
    },

    moduleNameMapper: {
        "^@/(.*)$": "<rootDir>/resources/js/$1",
        ".+\\.(css|styl|less|sass|scss|png|jpg|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga)$": "jest-transform-stub"
    },

    setupFiles: [
        'dotenv/config'
    ],

    setupFilesAfterEnv: [
        "./resources/js/tests/setupTests.js"
    ]
};
