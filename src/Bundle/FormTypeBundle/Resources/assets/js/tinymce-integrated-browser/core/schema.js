import Ajv from "ajv/dist/jtd"

const ajv = new Ajv();
const validate = ajv.compile({
    discriminator: "mceAction",
    mapping: {
        insertImage: {
            properties: {
                image: {
                    properties: {
                        id: {type: "string"},
                        uri: {type: "string"},
                        title: {type: "string"}
                    }
                }
            }
        },
        insertVideo: {
            properties: {
                video: {
                    properties: {
                        id: {type: "string"},
                        poster: {type: "string"},
                        uri: {type: "string"},
                        mine: {type: "string"}
                    }
                }
            }
        }
    }
})

export {
    validate
}
