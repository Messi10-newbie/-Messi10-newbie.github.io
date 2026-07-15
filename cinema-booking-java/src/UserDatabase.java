import java.io.*;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.*;

public class UserDatabase {

    private static final String FILE = "users.txt";

    public static void initDefaults() {
        File f = new File(FILE);
        if (!f.exists()) {
            List<String> defaults = Arrays.asList(
                "admin|" + hash("admin123") + "|admin",
                "john|"  + hash("john123")  + "|customer",
                "alice|" + hash("alice123") + "|customer"
            );
            saveLines(defaults);
        } else {
            migratePasswords();
        }
    }

    private static void migratePasswords() {
        List<String> lines = loadLines();
        boolean changed = false;
        List<String> updated = new ArrayList<>();
        for (String line : lines) {
            String[] p = line.split("\\|");
            if (p.length >= 3 && !isHashed(p[1])) {
                updated.add(p[0] + "|" + hash(p[1]) + "|" + p[2]);
                changed = true;
            } else {
                updated.add(line);
            }
        }
        if (changed) saveLines(updated);
    }

    private static boolean isHashed(String s) {
        return s != null && s.matches("[0-9a-f]{64}");
    }

    static String hash(String password) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            byte[] bytes = md.digest(password.getBytes(java.nio.charset.StandardCharsets.UTF_8));
            StringBuilder sb = new StringBuilder();
            for (byte b : bytes) sb.append(String.format("%02x", b));
            return sb.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException(e);
        }
    }

    public static String authenticate(String username, String password) {
        String hashed = hash(password);
        for (String line : loadLines()) {
            String[] p = line.split("\\|");
            if (p.length >= 3 && p[0].equals(username) && p[1].equals(hashed)) {
                return p[2];
            }
        }
        return null;
    }

    public static boolean userExists(String username) {
        for (String line : loadLines()) {
            String[] p = line.split("\\|");
            if (p.length >= 1 && p[0].equalsIgnoreCase(username)) return true;
        }
        return false;
    }

    public static boolean register(String username, String password) {
        if (userExists(username)) return false;
        List<String> lines = loadLines();
        lines.add(username + "|" + hash(password) + "|customer");
        saveLines(lines);
        return true;
    }

    public static List<String[]> getAllUsers() {
        List<String[]> result = new ArrayList<>();
        for (String line : loadLines()) {
            String[] p = line.split("\\|");
            if (p.length >= 3) result.add(new String[]{p[0], p[2]});
        }
        return result;
    }

    public static boolean deleteUser(String username) {
        List<String> lines = loadLines();
        boolean removed = lines.removeIf(l -> l.startsWith(username + "|"));
        if (removed) saveLines(lines);
        return removed;
    }

    private static List<String> loadLines() {
        List<String> lines = new ArrayList<>();
        try (BufferedReader br = new BufferedReader(new FileReader(FILE))) {
            String line;
            while ((line = br.readLine()) != null) {
                if (!line.isBlank()) lines.add(line.trim());
            }
        } catch (IOException ignored) {}
        return lines;
    }

    private static void saveLines(List<String> lines) {
        try (PrintWriter pw = new PrintWriter(new FileWriter(FILE))) {
            for (String l : lines) pw.println(l);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
