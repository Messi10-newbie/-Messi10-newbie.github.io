import java.io.*;
import java.util.HashMap;

public class UserDatabase {
    private static final String FILE_NAME = "users.txt";

    public static void saveUser(String username, String password) {
        try (BufferedWriter writer = new BufferedWriter(new FileWriter(FILE_NAME, true))) {
            writer.write(username + "," + password);
            writer.newLine();
        } catch (IOException e) {
            System.err.println("Error saving user: " + e.getMessage());
        }
    }

    public static HashMap<String, String> loadUsers() {
        HashMap<String, String> userMap = new HashMap<>();
        File file = new File(FILE_NAME);
        if (!file.exists()) return userMap;

        try (BufferedReader reader = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = reader.readLine()) != null) {
                String[] parts = line.split(",");
                if (parts.length == 2) {
                    userMap.put(parts[0], parts[1]);
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading users: " + e.getMessage());
        }
        return userMap;
    }

    public static boolean userExists(String username) {
        return loadUsers().containsKey(username);
    }
}
